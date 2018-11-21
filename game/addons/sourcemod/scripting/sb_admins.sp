#include <sourcemod>
#include <sourcebans>
#include <sb_admins>

#pragma newdecls required
#pragma semicolon 1

//#define _DEBUG

public Plugin myinfo =
{
    name        = "SourceBans: Admins",
    author      = "GameConnect",
    description = "Advanced admin management for the Source engine",
    version     = SB_VERSION,
    url         = "http://www.sourcebans.net"
};


/**
 * Globals
 */
bool g_PlayerAuth[MAXPLAYERS + 1]; // Whether a player has been "pre-authed"
bool g_RequireSiteLogin;
int g_AdminId[MAXPLAYERS + 1];
int g_PlayerSeq[MAXPLAYERS + 1];       // Player-specific sequence numbers
int g_RebuildCachePart[3] = {0};       // Cache part sequence numbers
int g_Sequence            = 0;
int g_ServerId;


/**
 * Plugin Forwards
 */
public APLRes AskPluginLoad2(Handle myself, bool late, char[] error, int err_max)
{
    CreateNative("SB_GetAdminId", Native_GetAdminId);
    RegPluginLibrary("sb_admins");

    return APLRes_Success;
}

public void OnPluginStart()
{
    LoadTranslations("common.phrases");

    // Account for late loading
    if (LibraryExists("sourcebans")) {
        SB_Init();
    }
}

public Action OnLogAction(Handle source, Identity ident, int client, int target, const char[] message)
{
    if (!SB_IsConnected()) {
        return Plugin_Continue;
    }

    int adminId = (client <= 0) ? 0 : SB_GetAdminId(client);
    char adminIp[16], ip[16], name[MAX_NAME_LENGTH + 1], steam[20];
    if (client > 0 && IsClientInGame(client)) {
        GetClientIP(client, adminIp, sizeof(adminIp));
    }
    if (target > 0 && IsClientInGame(target)) {
        GetClientAuthId(target, AuthId_Steam3, steam, sizeof(steam));
        GetClientIP(target,   ip,   sizeof(ip));
        GetClientName(target, name, sizeof(name));
    }

    char escapedMessage[256], escapedName[MAX_NAME_LENGTH * 2 + 1], sQuery[1024];
    SB_Escape(message, escapedMessage, sizeof(escapedMessage));
    SB_Escape(name,    escapedName,    sizeof(escapedName));
    Format(sQuery, sizeof(sQuery), "INSERT INTO sb_actions (admin_id, server_id, name, steam, ip, message, admin_ip, create_time) \
                                    VALUES (NULLIF(%i, 0), %i, NULLIF('%s', ''), NULLIF('%s', ''), NULLIF('%s', ''), '%s', '%s', NOW())",
                                    adminId, g_ServerId, escapedName, steam, ip, escapedMessage, adminIp);

    SB_Execute(sQuery);
    return Plugin_Continue;
}

public void OnRebuildAdminCache(AdminCachePart part)
{
    // Mark this part of the cache as being rebuilt.  This is used by the
    // callback system to determine whether the results should still be
    // used.
    int sequence             = ++g_Sequence;
    g_RebuildCachePart[part] = sequence;

    // If we don't have a database connection, we can't do any lookups just yet.
    if (!SB_IsConnected()) {
        SB_Connect();
        return;
    }

    if (part == AdminCache_Admins) {
        FetchAdmins();
    }
    else if (part == AdminCache_Groups) {
        FetchGroups(sequence);
    }
    else if (part == AdminCache_Overrides) {
        FetchOverrides(sequence);
    }
}


/**
 * SourceBans Forwards
 */
public void SB_OnInit()
{
    g_RequireSiteLogin = !!SB_GetConfigInt("RequireSiteLogin");
    g_ServerId         = SB_GetServerId();

    // See if we need to get any of the cache stuff now.
    int sequence;
    if ((sequence = g_RebuildCachePart[AdminCache_Admins])) {
        FetchAdmins();
    }
    if ((sequence = g_RebuildCachePart[AdminCache_Groups])) {
        FetchGroups(sequence);
    }
    if ((sequence = g_RebuildCachePart[AdminCache_Overrides])) {
        FetchOverrides(sequence);
    }
}


/**
 * Client Forwards
 */
public bool OnClientConnect(int client, char[] rejectmsg, int maxlen)
{
    g_AdminId[client]    = 0;
    g_PlayerSeq[client]  = 0;
    g_PlayerAuth[client] = false;
    return true;
}

public void OnClientDisconnect(int client)
{
    g_AdminId[client]    = 0;
    g_PlayerSeq[client]  = 0;
    g_PlayerAuth[client] = false;
}

public Action OnClientPreAdminCheck(int client)
{
    g_PlayerAuth[client] = true;

    // Play nice with other plugins.  If there's no database, don't delay the
    // connection process.  Unfortunately, we can't attempt anything else and
    // we just have to hope either the database is waiting or someone will type
    // sm_reloadadmins.
    if (!SB_IsConnected()) {
        return Plugin_Continue;
    }

    // Similarly, if the cache is in the process of being rebuilt, don't delay
    // the client's normal connection flow.  The database will soon auth the client
    // normally.
    if (g_RebuildCachePart[AdminCache_Admins]) {
        return Plugin_Continue;
    }

    // If someone has already assigned an admin ID (bad bad bad), don't
    // bother waiting.
    if (GetUserAdmin(client) != INVALID_ADMIN_ID) {
        return Plugin_Continue;
    }

    FetchAdmin(client);
    return Plugin_Handled;
}


/**
 * Query Callbacks
 */
public void Query_ReceiveAdmin(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    int client = pack.ReadCell();

    // Check if this is the latest result request.
    int sequence = pack.ReadCell();
    if (g_PlayerSeq[client] != sequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (results == null) {
        char query[1024];
        pack.ReadString(query, sizeof(query));
        LogError("SQL error receiving admin: %s", error);
        LogError("Query dump: %s", query);
        RunAdminCacheChecks(client);
        NotifyPostAdminCheck(client);
        delete pack;
        return;
    }

    int num_admins = results.RowCount;
    if (num_admins == 0) {
        RunAdminCacheChecks(client);
        NotifyPostAdminCheck(client);
        delete pack;
        return;
    }

    int adminId;
    AdminId admin;
    char auth[8], identity[65], name[MAX_NAME_LENGTH + 1], password[65];

    // Cache admin info -- [0] = db id, [1] = cache id, [2] = groups
    int[][] admin_lookup = new int[num_admins][3];
    int total_admins = 0;

    while (results.FetchRow()) {
        adminId = results.FetchInt(0);
        results.FetchString(1, name,     sizeof(name));
        results.FetchString(2, auth,     sizeof(auth));
        results.FetchString(3, identity, sizeof(identity));
        results.FetchString(4, password, sizeof(password));

        // For dynamic admins we clear anything already in the cache.
        if ((admin = FindAdminByIdentity(auth, identity)) != INVALID_ADMIN_ID) {
            RemoveAdmin(admin);
        }

        admin = CreateAdmin(name);
        if (!admin.BindIdentity(auth, identity)) {
            LogError("Could not bind admin (auth \"%s\") (identity \"%s\")", auth, identity);
            continue;
        }

        admin_lookup[total_admins][0] = adminId;
        admin_lookup[total_admins][1] = view_as<int>(admin);
        admin_lookup[total_admins][2] = results.FetchInt(5);
        total_admins++;

        #if defined _DEBUG
        PrintToServer("%sFound admin (%d,%s,%s,%s,%s):%d:%d", SB_PREFIX, adminId, auth, identity, password, name, admin, admin_lookup[total_admins-1][2]);
        #endif

        // See if this admin wants a password
        if (password[0]) {
            admin.SetPassword(password);
        }
    }

    // Try binding the admin.
    RunAdminCacheChecks(client);
    admin           = GetUserAdmin(client);
    adminId         = 0;
    int group_count = 0;

    for (int i = 0; i < total_admins; i++) {
        if (admin_lookup[i][1] == view_as<int>(admin)) {
            adminId     = admin_lookup[i][0];
            group_count = admin_lookup[i][2];
            break;
        }
    }

    #if defined _DEBUG
    PrintToServer("%sBinding client (%d, %d) resulted in: (%d, %d, %d)", SB_PREFIX, client, sequence, adminId, admin, group_count);
    #endif

    // Store admin ID locally
    g_AdminId[client] = adminId;

    // If we can't verify that we assigned a database admin, or the admin has no
    // groups, don't bother doing anything.
    if (!adminId || !group_count) {
        NotifyPostAdminCheck(client);
        delete pack;
        return;
    }

    // The admin has groups -- we need to fetch them!
    char query[1024];
    Format(query, sizeof(query), "SELECT     g.name \
                                  FROM       sb_server_groups         AS g \
                                  INNER JOIN sb_admins_server_groups  AS ag ON ag.group_id = g.id \
                                  INNER JOIN sb_servers_server_groups AS sg ON sg.group_id = g.id \
                                  WHERE      ag.admin_id  = %i \
                                    AND      sg.server_id = %i",
                                  adminId, g_ServerId);

    pack.Reset();
    pack.WriteCell(client);
    pack.WriteCell(sequence);
    pack.WriteCell(admin);
    pack.WriteString(query);

    SB_Query(Query_ReceiveAdminGroups, query, pack, DBPrio_High);
}

public void Query_ReceiveAdminGroups(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    int client = pack.ReadCell();

    // Make sure it's the same client.
    int sequence = pack.ReadCell();
    if (g_PlayerSeq[client] != sequence) {
        delete pack;
        return;
    }

    AdminId admin = pack.ReadCell();

    // Someone could have sneakily changed the admin id while we waited.
    if (GetUserAdmin(client) != admin) {
        NotifyPostAdminCheck(client);
        delete pack;
        return;
    }

    // See if we got results.
    if (results == null) {
        char query[1024];
        pack.ReadString(query, sizeof(query));
        LogError("SQL error receiving admin groups: %s", error);
        LogError("Query dump: %s", query);
        NotifyPostAdminCheck(client);
        delete pack;
        return;
    }

    GroupId group;
    char name[33];

    while (results.FetchRow()) {
        results.FetchString(0, name, sizeof(name));

        if ((group = FindAdmGroup(name)) == INVALID_GROUP_ID) {
            continue;
        }

        #if defined _DEBUG
        PrintToServer("%sBinding admin group (%d, %d, %d, %s, %d)", SB_PREFIX, client, sequence, admin, name, group);
        #endif

        admin.InheritGroup(group);
    }

    // We're DONE! Omg.
    NotifyPostAdminCheck(client);
    delete pack;
}

public void Query_ReceiveGroups(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Check if this is the latest result request.
    int sequence = pack.ReadCell();
    if (g_RebuildCachePart[AdminCache_Groups] != sequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (results == null) {
        char query[1024];
        pack.ReadString(query, sizeof(query));
        LogError("SQL error receiving groups: %s", error);
        LogError("Query dump: %s", query);
        delete pack;
        return;
    }

    // Now start fetching groups.
    int immunity;
    char flags[33], name[33];

    while (results.FetchRow()) {
        results.FetchString(0, name,  sizeof(name));
        results.FetchString(1, flags, sizeof(flags));
        immunity = results.FetchInt(2);

        #if defined _DEBUG
        PrintToServer("%sAdding group (%d, %s, %s)", SB_PREFIX, immunity, flags, name);
        #endif

        // Find or create the group
        GroupId group;
        if ((group = FindAdmGroup(name)) == INVALID_GROUP_ID) {
            group = CreateAdmGroup(name);
        }

        // Add flags from the database to the group
        int num_flag_chars = strlen(flags);
        AdminFlag flag;
        for (int i = 0; i < num_flag_chars; i++) {
            if (!FindFlagByChar(flags[i], flag)) {
                continue;
            }
            group.SetFlag(flag, true);
        }

        group.ImmunityLevel = immunity;
    }

    // It's time to get the group override list.
    char query[1024];
    Format(query, sizeof(query), "SELECT     g.name, go.type, go.name, go.access \
                                  FROM       sb_server_group_overrides AS go \
                                  INNER JOIN sb_server_groups          AS g  ON g.id = go.group_id \
                                  INNER JOIN sb_servers_server_groups  AS sg ON sg.group_id = g.id \
                                  WHERE      sg.server_id = %i \
                                  ORDER BY   g.id DESC",
                                  g_ServerId);

    pack.Reset();
    pack.WriteCell(sequence);
    pack.WriteString(query);

    SB_Query(Query_ReceiveGroupOverrides, query, pack, DBPrio_High);
}

public void Query_ReceiveGroupOverrides(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Check if this is the latest result request.
    int sequence = pack.ReadCell();
    if (g_RebuildCachePart[AdminCache_Groups] != sequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (results == null) {
        char query[1024];
        pack.ReadString(query, sizeof(query));
        LogError("SQL error receiving group overrides: %s", error);
        LogError("Query dump: %s", query);
        delete pack;
        return;
    }

    // Fetch the overrides.
    GroupId group;
    char access[8], command[65], name[65], type[8];

    while (results.FetchRow()) {
        results.FetchString(0, name,    sizeof(name));
        results.FetchString(1, type,    sizeof(type));
        results.FetchString(2, command, sizeof(command));
        results.FetchString(3, access,  sizeof(access));

        // Find the group.  This is actually faster than doing the ID lookup.
        if ((group = FindAdmGroup(name)) == INVALID_GROUP_ID) {
            // Oh well, just ignore it.
            continue;
        }

        OverrideType o_type = Override_Command;
        if (StrEqual(type, "group")) {
            o_type = Override_CommandGroup;
        }

        OverrideRule o_rule = Command_Deny;
        if (StrEqual(access, "allow")) {
            o_rule = Command_Allow;
        }

        #if defined _DEBUG
        PrintToServer("%sAddAdmGroupCmdOverride(%d, %s, %d, %d)", SB_PREFIX, group, command, o_type, o_rule);
        #endif

        group.AddCommandOverride(command, o_type, o_rule);
    }

    // It's time to get the group immunity list.
    char query[1024];
    Format(query, sizeof(query), "SELECT     g1.name, g2.name \
                                  FROM       sb_server_group_immunity AS gi \
                                  INNER JOIN sb_server_groups         AS g1 ON g1.id = gi.group_id \
                                  INNER JOIN sb_server_groups         AS g2 ON g2.id = gi.other_id \
                                  INNER JOIN sb_servers_server_groups AS sg ON sg.group_id = gi.group_id \
                                  WHERE      sg.server_id = %i",
                                  g_ServerId);

    pack.Reset();
    pack.WriteCell(sequence);
    pack.WriteString(query);

    SB_Query(Query_ReceiveGroupImmunity, query, pack, DBPrio_High);
}

public void Query_ReceiveGroupImmunity(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Check if this is the latest result request.
    int sequence = pack.ReadCell();
    if (g_RebuildCachePart[AdminCache_Groups] != sequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (results == null) {
        char query[1024];
        pack.ReadString(query, sizeof(query));
        LogError("SQL error receiving group immunity: %s", error);
        LogError("Query dump: %s", query);
        delete pack;
        return;
    }

    // We're done with the pack forever.
    delete pack;

    GroupId group, other;
    char group1[33], group2[33];

    while (results.FetchRow()) {
        results.FetchString(0, group1, sizeof(group1));
        results.FetchString(1, group2, sizeof(group2));

        if ((group = FindAdmGroup(group1)) == INVALID_GROUP_ID
            || (other = FindAdmGroup(group2)) == INVALID_GROUP_ID) {
            continue;
        }

        #if defined _DEBUG
        PrintToServer("%sSetAdmGroupImmuneFrom(%d, %d)", SB_PREFIX, group, other);
        #endif

        group.AddGroupImmunity(other);
    }

    // Clear the sequence so another connect doesn't refetch
    g_RebuildCachePart[AdminCache_Groups] = 0;
}

public void Query_ReceiveOverrides(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    int sequence = pack.ReadCell();

    // Check if this is the latest result request.
    if (g_RebuildCachePart[AdminCache_Overrides] != sequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (results == null) {
        char query[1024];
        pack.ReadString(query, sizeof(query));
        LogError("SQL error receiving overrides: %s", error);
        LogError("Query dump: %s", query);
        delete pack;
        return;
    }

    // We're done with you, now.
    delete pack;

    int flag_bits;
    char flags[32], name[33], type[8];

    while (results.FetchRow()) {
        results.FetchString(0, type,  sizeof(type));
        results.FetchString(1, name,  sizeof(name));
        results.FetchString(2, flags, sizeof(flags));

        #if defined _DEBUG
        PrintToServer("%sAdding override (%s, %s, %s)", SB_PREFIX, type, name, flags);
        #endif

        flag_bits = ReadFlagString(flags);
        if (StrEqual(type, "command")) {
            AddCommandOverride(name, Override_Command, flag_bits);
        } else if (StrEqual(type, "group")) {
            AddCommandOverride(name, Override_CommandGroup, flag_bits);
        }
    }

    // Clear the sequence so another connect doesn't refetch
    g_RebuildCachePart[AdminCache_Overrides] = 0;
}


/**
 * Natives
 */
public int Native_GetAdminId(Handle plugin, int numParams)
{
    int client = GetNativeCell(1);
    if (!client || !IsClientInGame(client)) {
        return 0;
    }

    return g_AdminId[client];
}


/**
 * Stocks
 */
void FetchAdmin(int client)
{
    char ip[16], name[MAX_NAME_LENGTH + 1], steam[20];

    // Get authentication information from the client.
    GetClientIP(client,   ip,   sizeof(ip));
    GetClientName(client, name, sizeof(name));

    if (!GetClientAuthId(client, AuthId_Steam3, steam, sizeof(steam))
        || StrEqual(steam, "STEAM_ID_LAN")) {
        steam[5] = '\0';
    }

    // Construct the query using the information the client gave us.
    char condition[30];
    if (g_RequireSiteLogin) {
        strcopy(condition, sizeof(condition), "AND a.login_time IS NOT NULL");
    }

    char escapedName[MAX_NAME_LENGTH * 2 + 1], query[1024];
    SB_Escape(name, escapedName, sizeof(escapedName));
    Format(query, sizeof(query), "SELECT     a.id, a.name, a.auth, a.identity, a.server_password, COUNT(ag.group_id) \
                                  FROM       sb_admins                AS a \
                                  INNER JOIN sb_admins_server_groups  AS ag ON ag.admin_id = a.id \
                                  INNER JOIN sb_servers_server_groups AS sg ON sg.group_id = ag.group_id \
                                  WHERE      ((a.auth = '%s' AND a.identity REGEXP '^\\\\[U:[0-4]:%s$') \
                                              OR (a.auth = '%s' AND '%s' REGEXP REPLACE(REPLACE(a.identity, '.', '\\\\.'), '.0', '..{1,3}')) \
                                              OR (a.auth = '%s' AND a.identity = '%s')) \
                                    AND      sg.server_id = %i %s \
                                  GROUP BY   a.id",
                                  AUTHMETHOD_STEAM, steam[5], AUTHMETHOD_IP, ip, AUTHMETHOD_NAME, escapedName, g_ServerId, condition);

    // Send the actual query.
    g_PlayerSeq[client] = ++g_Sequence;

    DataPack pack = new DataPack();
    pack.WriteCell(client);
    pack.WriteCell(g_PlayerSeq[client]);
    pack.WriteString(query);

    #if defined _DEBUG
    PrintToServer("%sSending admin query: %s", SB_PREFIX, query);
    #endif

    SB_Query(Query_ReceiveAdmin, query, pack, DBPrio_High);
}

void FetchAdmins()
{
    for (int i = 1; i <= MaxClients; i++) {
        if (g_PlayerAuth[i] && GetUserAdmin(i) == INVALID_ADMIN_ID) {
            FetchAdmin(i);
        }
    }

    // This round of updates is done.  Go in peace.
    g_RebuildCachePart[AdminCache_Admins] = 0;
}

void FetchGroups(int sequence)
{
    char query[1024];
    Format(query, sizeof(query), "SELECT     g.name, g.flags, g.immunity \
                                  FROM       sb_server_groups         AS g \
                                  INNER JOIN sb_servers_server_groups AS sg ON sg.group_id = g.id \
                                  WHERE      sg.server_id = %i",
                                  g_ServerId);

    DataPack pack = new DataPack();
    pack.WriteCell(sequence);
    pack.WriteString(query);

    #if defined _DEBUG
    PrintToServer("%sSending groups query: %s", SB_PREFIX, query);
    #endif

    SB_Query(Query_ReceiveGroups, query, pack, DBPrio_High);
}

void FetchOverrides(int sequence)
{
    char query[1024];
    Format(query, sizeof(query), "SELECT type, name, flags \
                                  FROM   sb_overrides");

    DataPack pack = new DataPack();
    pack.WriteCell(sequence);
    pack.WriteString(query);

    #if defined _DEBUG
    PrintToServer("%sSending overrides query: %s", SB_PREFIX, query);
    #endif

    SB_Query(Query_ReceiveOverrides, query, pack, DBPrio_High);
}
