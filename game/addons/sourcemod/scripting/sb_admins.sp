/**
 * SourceBans Admins Plugin
 *
 * @author GameConnect
 * @version 2.0.0
 * @copyright SourceBans (C)2007-2016 GameConnect.net.  All rights reserved.
 * @package SourceBans
 * @link http://www.sourcebans.net
 */

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
int g_iAdminId[MAXPLAYERS + 1];
int g_iPlayerSeq[MAXPLAYERS + 1];		// Player-specific sequence numbers
int g_iRebuildCachePart[3] = {0};		// Cache part sequence numbers
int g_iSequence            = 0;
int g_iServerId;
bool g_bPlayerAuth[MAXPLAYERS + 1];	// Whether a player has been "pre-authed"
bool g_bRequireSiteLogin;
char g_sServerIp[16];


/**
 * Plugin Forwards
 */
public APLRes AskPluginLoad2(Handle myself, bool late, char[] error, int err_max)
{
    CreateNative("SB_GetAdminId",     Native_GetAdminId);
    CreateNative("SB_AddAdmin",       Native_AddAdmin);
    CreateNative("SB_DeleteAdmin",    Native_DeleteAdmin);
    CreateNative("SB_AddGroup",       Native_AddGroup);
    CreateNative("SB_DeleteGroup",    Native_DeleteGroup);
    CreateNative("SB_SetAdminGroups", Native_SetAdminGroups);
    RegPluginLibrary("sb_admins");

    return APLRes_Success;
}

public void OnPluginStart()
{
    RegAdminCmd("sb_addadmin",       Command_AddAdmin,       ADMFLAG_ROOT, "Adds an admin to SourceBans");
    RegAdminCmd("sb_deladmin",       Command_DelAdmin,       ADMFLAG_ROOT, "Removes an admin from SourceBans");
    RegAdminCmd("sb_addgroup",       Command_AddGroup,       ADMFLAG_ROOT, "Adds a group to SourceBans");
    RegAdminCmd("sb_delgroup",       Command_DelGroup,       ADMFLAG_ROOT, "Removes a group from SourceBans");
    RegAdminCmd("sb_setadmingroups", Command_SetAdminGroups, ADMFLAG_ROOT, "Sets an admin's groups in SourceBans");

    LoadTranslations("common.phrases");
    LoadTranslations("sourcebans.phrases");
    LoadTranslations("sqladmins.phrases");

    // Account for late loading
    if (LibraryExists("sourcebans")) {
        SB_Init();
    }
}

#if SOURCEMOD_V_MAJOR >= 1 && SOURCEMOD_V_MINOR >= 8
public void OnRebuildAdminCache(AdminCachePart part)
#else
public int OnRebuildAdminCache(AdminCachePart part)
#endif
{
    // Mark this part of the cache as being rebuilt.  This is used by the
    // callback system to determine whether the results should still be
    // used.
    int iSequence             = ++g_iSequence;
    g_iRebuildCachePart[part] = iSequence;

    // If we don't have a database connection, we can't do any lookups just yet.
    if (!SB_IsConnected()) {
        SB_Connect();
        return;
    }

    if (part      == AdminCache_Admins) {
        FetchAdmins();
    }
    else if (part == AdminCache_Groups) {
        FetchGroups(iSequence);
    }
    else if (part == AdminCache_Overrides) {
        FetchOverrides(iSequence);
    }
}

public void OnConfigsExecuted()
{
    if (DisablePlugin("admin-sql-prefetch") | DisablePlugin("admin-sql-threaded") | DisablePlugin("sql-admin-manager")) {
        // Reload admins
        DumpAdminCache(AdminCache_Groups, true);
        DumpAdminCache(AdminCache_Overrides, true);
    }
}

public Action OnLogAction(Handle source, Identity ident, int client, int target, const char[] message)
{
    if (!SB_IsConnected()) {
        return Plugin_Continue;
    }

    char sAdminIp[16] = "", sAuth[20] = "", sEscapedMessage[256], sEscapedName[MAX_NAME_LENGTH * 2 + 1], sIp[16] = "", sName[MAX_NAME_LENGTH + 1] = "", sQuery[1024];
    int iAdminId = SB_GetAdminId(client);
    if (target > 0 && IsClientInGame(target)) {
        GetClientAuthId(target, AuthId_Steam2, sAuth, sizeof(sAuth));
        GetClientIP(target,                    sIp,   sizeof(sIp));
        GetClientName(target,                  sName, sizeof(sName));
    }

    if (client > 0 && IsClientInGame(client)) {
        GetClientIP(client, sAdminIp, sizeof(sAdminIp));
    } else {
        sAdminIp = g_sServerIp;
    }

    SB_Escape(message, sEscapedMessage, sizeof(sEscapedMessage));
    SB_Escape(sName,   sEscapedName,    sizeof(sEscapedName));
    Format(sQuery, sizeof(sQuery), "INSERT INTO {{actions}} (name, steam, ip, message, server_id, admin_id, admin_ip, create_time) \
                                    VALUES      (NULLIF('%s', ''), NULLIF('%s', ''), NULLIF('%s', ''), '%s', %i, NULLIF(%i, 0), '%s', UNIX_TIMESTAMP())",
                                    sEscapedName, sAuth, sIp, sEscapedMessage, g_iServerId, iAdminId, sAdminIp);
    SB_Execute(sQuery);
    return Plugin_Continue;
}


/**
 * Client Forwards
 */
public bool OnClientConnect(int client, char[] rejectmsg, int maxlen)
{
    g_iAdminId[client]    = 0;
    g_iPlayerSeq[client]  = 0;
    g_bPlayerAuth[client] = false;
    return true;
}

public void OnClientDisconnect(int client)
{
    g_iAdminId[client]    = 0;
    g_iPlayerSeq[client]  = 0;
    g_bPlayerAuth[client] = false;
}

public Action OnClientPreAdminCheck(int client)
{
    g_bPlayerAuth[client] = true;

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
    if (g_iRebuildCachePart[AdminCache_Admins]) {
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
 * SourceBans Forwards
 */
public void SB_OnConnect(Database db)
{
    g_iServerId = SB_GetConfigValue("ServerID");

    // See if we need to get any of the cache stuff now.
    int iSequence;
    if ((iSequence = g_iRebuildCachePart[AdminCache_Admins])) {
        FetchAdmins();
    }
    if ((iSequence = g_iRebuildCachePart[AdminCache_Groups])) {
        FetchGroups(iSequence);
    }
    if ((iSequence = g_iRebuildCachePart[AdminCache_Overrides])) {
        FetchOverrides(iSequence);
    }
}

public void SB_OnReload()
{
    g_bRequireSiteLogin = SB_GetConfigValue("RequireSiteLogin");

    SB_GetConfigString("ServerIP", g_sServerIp, sizeof(g_sServerIp));
}


/**
 * Commands
 */
public Action Command_AddAdmin(int client, int args)
{
    if (args < 4) {
        ReplyToCommand(client, "%sUsage: sb_addadmin <name> <authtype> <identity> [password] [group1] ... [group N]", SB_PREFIX);
        return Plugin_Handled;
    }
    if (!SB_IsConnected()) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Could not connect to database");
        return Plugin_Handled;
    }

    int iLen;
    char sArg[256], sIdentity[65], sName[MAX_NAME_LENGTH + 1], sPassword[65], sType[16];
    GetCmdArgString(sArg, sizeof(sArg));
    iLen  = BreakString(sArg,       sName,     sizeof(sName));
    iLen += BreakString(sArg[iLen], sType,     sizeof(sType));
    iLen += BreakString(sArg[iLen], sIdentity, sizeof(sIdentity));

    if (sArg[iLen]) {
        iLen += BreakString(sArg[iLen], sPassword, sizeof(sPassword));
    } else {
        sPassword[0] = '\0';
    }

    SB_AddAdmin(client, sName, sType, sIdentity, sPassword, sArg[iLen]);
    return Plugin_Handled;
}

public Action Command_DelAdmin(int client, int args)
{
    if (args < 2) {
        ReplyToCommand(client, "%sUsage: sb_deladmin <authtype> <identity>", SB_PREFIX);
        return Plugin_Handled;
    }
    if (!SB_IsConnected()) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Could not connect to database");
        return Plugin_Handled;
    }

    char sIdentity[65], sType[16];
    GetCmdArg(1, sType,     sizeof(sType));
    GetCmdArg(2, sIdentity, sizeof(sIdentity));

    SB_DeleteAdmin(client, sType, sIdentity);
    return Plugin_Handled;
}

public Action Command_AddGroup(int client, int args)
{
    if (args < 2) {
        ReplyToCommand(client, "%sUsage: sb_addgroup <name> <flags> [immunity]", SB_PREFIX);
        return Plugin_Handled;
    }
    if (!SB_IsConnected()) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Could not connect to database");
        return Plugin_Handled;
    }

    char sFlags[33], sName[33];
    GetCmdArg(1, sName,  sizeof(sName));
    GetCmdArg(2, sFlags, sizeof(sFlags));

    int iImmunity;
    if (args >= 3) {
        char sArg[32];
        GetCmdArg(3, sArg, sizeof(sArg));
        if (!StringToIntEx(sArg, iImmunity)) {
            ReplyToCommand(client, "%s%t", SB_PREFIX, "Invalid immunity");
            return Plugin_Handled;
        }
    }

    SB_AddGroup(client, sName, sFlags, iImmunity);
    return Plugin_Handled;
}

public Action Command_DelGroup(int client, int args)
{
    if (args < 1) {
        ReplyToCommand(client, "%sUsage: sb_delgroup <name>", SB_PREFIX);
        return Plugin_Handled;
    }
    if (!SB_IsConnected()) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Could not connect to database");
        return Plugin_Handled;
    }

    char sName[33];
    GetCmdArgString(sName, sizeof(sName));

    // Strip quotes in case the user tries to use them
    StripQuotes(sName);

    SB_DeleteGroup(client, sName);
    return Plugin_Handled;
}

public Action Command_SetAdminGroups(int client, int args)
{
    if (args < 2) {
        ReplyToCommand(client, "%sUsage: sb_setadmingroups <authtype> <identity> [group1] ... [group N]", SB_PREFIX);
        return Plugin_Handled;
    }
    if (!SB_IsConnected()) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Could not connect to database");
        return Plugin_Handled;
    }

    int iLen;
    char sArg[256], sIdentity[65], sType[16];
    GetCmdArgString(sArg, sizeof(sArg));
    iLen  = BreakString(sArg,       sType,     sizeof(sType));
    iLen += BreakString(sArg[iLen], sIdentity, sizeof(sIdentity));

    if (!StrEqual(sType, AUTHMETHOD_STEAM) && !StrEqual(sType, AUTHMETHOD_IP) && !StrEqual(sType, AUTHMETHOD_NAME)) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Invalid authtype");
        return Plugin_Handled;
    }

    SB_SetAdminGroups(client, sType, sIdentity, sArg[iLen]);
    return Plugin_Handled;
}


/**
 * Query Callbacks
 */
public void Query_AddAdmin(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sGroups[512], sIdentity[65], sName[MAX_NAME_LENGTH + 1], sPassword[65], sType[16];
    int iAdmin = pack.ReadCell();
    pack.ReadString(sName,     sizeof(sName));
    pack.ReadString(sType,     sizeof(sType));
    pack.ReadString(sIdentity, sizeof(sIdentity));
    pack.ReadString(sPassword, sizeof(sPassword));
    pack.ReadString(sGroups,   sizeof(sGroups));
    delete pack;

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to retrieve the admin from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to retrieve the admin.", SB_PREFIX);
        }
        return;
    }
    if (results.RowCount) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Admin already exists");
        }
        return;
    }

    char sEscapedIdentity[129], sEscapedName[MAX_NAME_LENGTH * 2 + 1], sEscapedPassword[129], sQuery[1024];
    SB_Escape(sIdentity, sEscapedIdentity, sizeof(sEscapedIdentity));
    SB_Escape(sName,     sEscapedName,     sizeof(sEscapedName));
    SB_Escape(sPassword, sEscapedPassword, sizeof(sEscapedPassword));
    Format(sQuery, sizeof(sQuery), "INSERT INTO {{admins}} (name, auth, identity, server_password, create_time) \
                                    VALUES      ('%s', '%s', '%s', NULLIF('%s', ''), UNIX_TIMESTAMP())",
                                    sEscapedName, sType, sEscapedIdentity, sEscapedPassword);
    SB_Execute(sQuery);

    if (bPrint) {
        ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Admin added");
    }

    SB_SetAdminGroups(iAdmin, sType, sIdentity, sGroups);
}

public void Query_DelAdmin(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    int iAdmin = pack.ReadCell();
    delete pack;

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to retrieve the admin from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to retrieve the admin.", SB_PREFIX);
        }
        return;
    }
    if (!results.FetchRow()) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Admin not found");
        }
        return;
    }

    char sQuery[1024];
    int iAdminId = results.FetchInt(0);

    // Delete group bindings
    Format(sQuery, sizeof(sQuery), "DELETE FROM {{admins_server_groups}} \
                                    WHERE       admin_id = %i",
                                    iAdminId);
    SB_Execute(sQuery);

    Format(sQuery, sizeof(sQuery), "DELETE FROM {{admins}} \
                                    WHERE       id = %i",
                                    iAdminId);
    SB_Execute(sQuery);

    if (bPrint) {
        ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Admin deleted");
    }
}

public void Query_AddGroup(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sFlags[33], sName[33];
    int iAdmin    = pack.ReadCell();
    pack.ReadString(sName,  sizeof(sName));
    pack.ReadString(sFlags, sizeof(sFlags));
    int iImmunity = pack.ReadCell();
    delete pack;

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to retrieve the group from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to retrieve the group.", SB_PREFIX);
        }
        return;
    }
    if (results.RowCount) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Group already exists");
        }
        return;
    }

    char sEscapedFlags[65], sEscapedName[65], sQuery[1024];
    SB_Escape(sFlags, sEscapedFlags, sizeof(sEscapedFlags));
    SB_Escape(sName,  sEscapedName,  sizeof(sEscapedName));
    Format(sQuery, sizeof(sQuery), "INSERT INTO {{server_groups}} (name, flags, immunity) \
                                    VALUES ('%s', '%s', %i)",
                                    sEscapedName, sEscapedFlags, iImmunity);
    SB_Execute(sQuery);

    if (bPrint) {
        ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Group added");
    }
}

public void Query_DelGroup(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sQuery[1024];
    int iAdmin = pack.ReadCell();
    delete pack;

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to retrieve the group from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to retrieve the group.", SB_PREFIX);
        }
        return;
    }
    if (!results.FetchRow()) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Group not found");
        }
        return;
    }

    int iGroupId = results.FetchInt(0);

    // Delete admin inheritance for this group
    Format(sQuery, sizeof(sQuery), "DELETE FROM {{admins_server_groups}} \
                                    WHERE       group_id = %i",
                                    iGroupId);
    SB_Execute(sQuery);

    // Delete group overrides
    Format(sQuery, sizeof(sQuery), "DELETE FROM {{server_group_overrides}} \
                                    WHERE       group_id = %i",
                                    iGroupId);
    SB_Execute(sQuery);

    // Delete immunity
    Format(sQuery, sizeof(sQuery), "DELETE FROM {{server_groups_immunity}} \
                                    WHERE       group_id = %i \
                                       OR       other_id = %i",
                                    iGroupId, iGroupId);
    SB_Execute(sQuery);

    // Finally delete the group
    Format(sQuery, sizeof(sQuery), "DELETE FROM {{server_groups}} \
                                    WHERE       id = %i",
                                    iGroupId);
    SB_Execute(sQuery);

    if (bPrint) {
        ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Group deleted");
    }
}

public void Query_SetAdminGroups(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    int iAdmin = pack.ReadCell();

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to retrieve the admin from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to retrieve the admin.", SB_PREFIX);
        }

        delete pack;
        return;
    }
    if (!results.FetchRow()) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Admin not found");
        }

        delete pack;
        return;
    }

    char sQuery[1024];
    int iAdminId = results.FetchInt(0);

    // First delete all of the user's existing groups.
    Format(sQuery, sizeof(sQuery), "DELETE FROM {{admins_server_groups}} \
                                    WHERE       admin_id = %i",
                                    iAdmin);
    SB_Execute(sQuery);

    int iCount = pack.ReadCell();
    if (!iCount) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "SQL Admin groups reset");
        }

        delete pack;
        return;
    }

    char sEscapedName[65], sName[33];
    int iOrder = 0;
    while (iOrder < iCount) {
        pack.ReadString(sName, sizeof(sName));
        SB_Escape(sName, sEscapedName, sizeof(sEscapedName));
        Format(sQuery, sizeof(sQuery), "INSERT INTO {{admins_server_groups}} (admin_id, group_id, inherit_order) \
                                        VALUES      (%i, (SELECT id FROM {{server_groups}} WHERE name = '%s'), %i)",
                                        iAdminId, sEscapedName, ++iOrder);
        SB_Execute(sQuery);
    }

    delete pack;

    if (bPrint) {
        if (iOrder     == 1) {
            ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "Added group to user");
        }
        else if (iOrder > 1) {
            ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "Added groups to user", iOrder);
        }
    }
}

public void Query_SelectAdmin(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Check if this is the latest result request.
    int iClient   = pack.ReadCell(),
        iSequence = pack.ReadCell();
    if (!ParseClientFromSerial(iClient) || g_iPlayerSeq[iClient] != iSequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (error[0]) {
        char sQuery[1024];
        pack.ReadString(sQuery, sizeof(sQuery));
        LogError("SQL error receiving admin: %s", error);
        LogError("Query dump: %s", sQuery);
        RunAdminCacheChecks(iClient);
        NotifyPostAdminCheck(iClient);
        delete pack;
        return;
    }

    int iAccounts = results.RowCount;
    if (!iAccounts) {
        RunAdminCacheChecks(iClient);
        NotifyPostAdminCheck(iClient);
        delete pack;
        return;
    }

    // Cache admin info -- [0] = db id, [1] = cache id, [2] = groups
    int[][] iLookup = new int[iAccounts][3];
    int iAdmins = 0;

    int iAdminId;
    AdminId iAdmin;
    char sIdentity[65], sName[MAX_NAME_LENGTH + 1], sPassword[65], sType[8];
    while (results.FetchRow()) {
        iAdminId = results.FetchInt(0);
        results.FetchString(1, sName,     sizeof(sName));
        results.FetchString(2, sType,     sizeof(sType));
        results.FetchString(3, sIdentity, sizeof(sIdentity));
        results.FetchString(4, sPassword, sizeof(sPassword));

        // For dynamic admins we clear anything already in the cache.
        if ((iAdmin = FindAdminByIdentity(sType, sIdentity)) != INVALID_ADMIN_ID) {
            RemoveAdmin(iAdmin);
        }

        iAdmin = CreateAdmin(sName);
        if (!BindAdminIdentity(iAdmin, sType, sIdentity)) {
            LogError("Could not bind prefetched SQL admin (authtype \"%s\") (identity \"%s\")", sType, sIdentity);
            continue;
        }

        iLookup[iAdmins][0] = iAdminId;
        iLookup[iAdmins][1] = view_as<int>(iAdmin);
        iLookup[iAdmins][2] = results.FetchInt(5);
        iAdmins++;

        #if defined _DEBUG
        PrintToServer("%sFound SQL admin (%i,%s,%s,%s,%s):%i:%i", SB_PREFIX, iAdminId, sType, sIdentity, sPassword, sName, iAdmin, iLookup[iAdmins - 1][2]);
        #endif

        // See if this admin wants a password
        if (sPassword[0]) {
            SetAdminPassword(iAdmin, sPassword);
        }
    }

    // Try binding the admin.
    RunAdminCacheChecks(iClient);
    iAdmin      = GetUserAdmin(iClient);
    iAdminId    = 0;
    int iGroups = 0;

    for (int i = 0; i < iAdmins; i++) {
        if (iLookup[i][1] == view_as<int>(iAdmin)) {
            iAdminId = iLookup[i][0];
            iGroups  = iLookup[i][2];
            break;
        }
    }

    g_iAdminId[iClient] = iAdminId;

    #if defined _DEBUG
    PrintToServer("%sBinding client (%i, %i) resulted in: (%i, %i, %i)", SB_PREFIX, iClient, iSequence, iAdminId, iAdmin, iGroups);
    #endif

    // If we can't verify that we assigned a database admin, or the admin has no
    // groups, don't bother doing anything.
    if (!iAdminId || !iGroups) {
        NotifyPostAdminCheck(iClient);
        delete pack;
        return;
    }

    // The admin has groups -- we need to fetch them!
    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "SELECT    sg.name \
                                    FROM      {{server_groups}}         AS sg \
                                    LEFT JOIN {{admins_server_groups}}  AS ag ON ag.group_id = sg.id \
                                    LEFT JOIN {{servers_server_groups}} AS gs ON gs.group_id = sg.id \
                                    WHERE     ag.admin_id  = %i \
                                      AND     gs.server_id = %i",
                                    iAdminId, g_iServerId);

    pack.Reset();
    pack.WriteCell(ParseClientSerial(iClient));
    pack.WriteCell(iSequence);
    pack.WriteCell(iAdmin);
    pack.WriteString(sQuery);

    SB_Query(Query_SelectAdminGroups, sQuery, pack, DBPrio_High);
}

public void Query_SelectAdminGroups(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Make sure it's the same client.
    int iClient   = pack.ReadCell(),
        iSequence = pack.ReadCell();
    if (!ParseClientFromSerial(iClient) || g_iPlayerSeq[iClient] != iSequence) {
        delete pack;
        return;
    }

    // Someone could have sneakily changed the admin id while we waited.
    AdminId iAdmin = pack.ReadCell();
    if (GetUserAdmin(iClient) != iAdmin) {
        NotifyPostAdminCheck(iClient);
        delete pack;
        return;
    }

    // See if we got results.
    if (error[0]) {
        char sQuery[1024];
        pack.ReadString(sQuery, sizeof(sQuery));
        LogError("SQL error receiving admin: %s", error);
        LogError("Query dump: %s", sQuery);
        NotifyPostAdminCheck(iClient);
        delete pack;
        return;
    }

    GroupId iGroup;
    char sName[33];
    while (results.FetchRow()) {
        results.FetchString(0, sName, sizeof(sName));

        if ((iGroup = FindAdmGroup(sName)) == INVALID_GROUP_ID) {
            continue;
        }

        #if defined _DEBUG
        PrintToServer("%sBinding admin group (%i, %i, %i, %s, %i)", SB_PREFIX, iClient, iSequence, iAdmin, sName, iGroup);
        #endif

        AdminInheritGroup(iAdmin, iGroup);
    }

    // We're DONE! Omg.
    NotifyPostAdminCheck(iClient);
    delete pack;
}

public void Query_SelectGroups(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Check if this is the latest result request.
    int iSequence = pack.ReadCell();
    if (g_iRebuildCachePart[AdminCache_Groups] != iSequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (error[0]) {
        char sQuery[1024];
        pack.ReadString(sQuery, sizeof(sQuery));
        LogError("SQL error receiving groups: %s", error);
        LogError("Query dump: %s", sQuery);
        delete pack;
        return;
    }

    // Now start fetching groups.
    int iImmunity;
    char sFlags[33], sName[33];
    while (results.FetchRow()) {
        results.FetchString(0, sName,  sizeof(sName));
        results.FetchString(1, sFlags, sizeof(sFlags));
        iImmunity = results.FetchInt(2);

        #if defined _DEBUG
        PrintToServer("%sAdding group (%i, %s, %s)", SB_PREFIX, iImmunity, sFlags, sName);
        #endif

        // Find or create the group
        GroupId iGroup;
        if ((iGroup = FindAdmGroup(sName)) == INVALID_GROUP_ID) {
            iGroup = CreateAdmGroup(sName);
        }

        // Add flags from the database to the group
        AdminFlag iFlag;
        for (int i = 0, iLen = strlen(sFlags); i < iLen; i++) {
            if (FindFlagByChar(sFlags[i], iFlag)) {
                SetAdmGroupAddFlag(iGroup, iFlag, true);
            }
        }

        SetAdmGroupImmunityLevel(iGroup, iImmunity);
    }

    // It's time to get the group override list.
    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "SELECT    sg.name, go.type, go.name, go.access \
                                    FROM      {{server_group_overrides}} AS go \
                                    LEFT JOIN {{server_groups}}          AS sg ON go.group_id = sg.id \
                                    LEFT JOIN {{servers_server_groups}}  AS gs ON gs.group_id = sg.id \
                                    WHERE     gs.server_id = %i \
                                    ORDER BY  sg.id DESC",
                                    g_iServerId);

    pack.Reset();
    pack.WriteCell(iSequence);
    pack.WriteString(sQuery);

    SB_Query(Query_SelectGroupOverrides, sQuery, pack, DBPrio_High);
}

public void Query_SelectGroupOverrides(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Check if this is the latest result request.
    int iSequence = pack.ReadCell();
    if (g_iRebuildCachePart[AdminCache_Groups] != iSequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (error[0]) {
        char sQuery[1024];
        pack.ReadString(sQuery, sizeof(sQuery));
        LogError("SQL error receiving group overrides: %s", error);
        LogError("Query dump: %s", sQuery);
        delete pack;
        return;
    }

    // Fetch the overrides.
    GroupId iGroup;
    OverrideRule iRule;
    OverrideType iType;
    char sAccess[16], sCommand[64], sName[80], sType[16];
    while (results.FetchRow()) {
        results.FetchString(0, sName,    sizeof(sName));
        results.FetchString(1, sType,    sizeof(sType));
        results.FetchString(2, sCommand, sizeof(sCommand));
        results.FetchString(3, sAccess,  sizeof(sAccess));

        // Find the group. This is actually faster than doing the ID lookup.
        if ((iGroup = FindAdmGroup(sName)) == INVALID_GROUP_ID) {
            // Oh well, just ignore it.
            continue;
        }

        iRule = StrEqual(sAccess, "allow") ? Command_Allow         : Command_Deny;
        iType = StrEqual(sType,   "group") ? Override_CommandGroup : Override_Command;

        #if defined _DEBUG
        PrintToServer("%sAddAdmGroupCmdOverride(%i, %s, %i, %i)", SB_PREFIX, iGroup, sCommand, iType, iRule);
        #endif

        AddAdmGroupCmdOverride(iGroup, sCommand, iType, iRule);
    }

    // It's time to get the group immunity list.
    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "SELECT    sg1.name, sg2.name \
                                    FROM      {{server_groups_immunity}} AS gi \
                                    LEFT JOIN {{server_groups}}          AS sg1 ON sg1.id      = gi.group_id \
                                    LEFT JOIN {{server_groups}}          AS sg2 ON sg2.id      = gi.other_id \
                                    LEFT JOIN {{servers_server_groups}}  AS gs  ON gs.group_id = gi.group_id \
                                    WHERE     gs.server_id = %i",
                                    g_iServerId);

    pack.Reset();
    pack.WriteCell(iSequence);
    pack.WriteString(sQuery);

    SB_Query(Query_SelectGroupImmunity, sQuery, pack, DBPrio_High);
}

public void Query_SelectGroupImmunity(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Check if this is the latest result request.
    int iSequence = pack.ReadCell();
    if (g_iRebuildCachePart[AdminCache_Groups] != iSequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (error[0]) {
        char sQuery[1024];
        pack.ReadString(sQuery, sizeof(sQuery));
        LogError("SQL error receiving group immunity: %s", error);
        LogError("Query dump: %s", sQuery);
        delete pack;
        return;
    }

    // We're done with the pack forever.
    delete pack;

    while (results.FetchRow()) {
        char sGroup1[33], sGroup2[33];
        GroupId iGroup1, iGroup2;

        results.FetchString(0, sGroup1, sizeof(sGroup1));
        results.FetchString(1, sGroup2, sizeof(sGroup2));

        if ((iGroup1 = FindAdmGroup(sGroup1)) == INVALID_GROUP_ID || (iGroup2 = FindAdmGroup(sGroup2)) == INVALID_GROUP_ID) {
            continue;
        }

        SetAdmGroupImmuneFrom(iGroup1, iGroup2);
        #if defined _DEBUG
        PrintToServer("%sSetAdmGroupImmuneFrom(%i, %i)", SB_PREFIX, iGroup1, iGroup2);
        #endif
    }

    // Clear the sequence so another connect doesn't refetch
    g_iRebuildCachePart[AdminCache_Groups] = 0;
}

public void Query_SelectOverrides(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    // Check if this is the latest result request.
    int iSequence = pack.ReadCell();
    if (g_iRebuildCachePart[AdminCache_Overrides] != iSequence) {
        // Discard everything, since we're out of sequence.
        delete pack;
        return;
    }

    // If we need to use the results, make sure they succeeded.
    if (error[0]) {
        char sQuery[1024];
        pack.ReadString(sQuery, sizeof(sQuery));
        LogError("SQL error receiving overrides: %s", error);
        LogError("Query dump: %s", sQuery);
        delete pack;
        return;
    }

    // We're done with you, now.
    delete pack;

    char sFlags[32], sName[64], sType[64];
    while (results.FetchRow()) {
        results.FetchString(0, sType, sizeof(sType));
        results.FetchString(1, sName, sizeof(sName));
        results.FetchString(2, sFlags, sizeof(sFlags));

        #if defined _DEBUG
        PrintToServer("%sAdding override (%s, %s, %s)", SB_PREFIX, sType, sName, sFlags);
        #endif

        if (StrEqual(sType,      "command")) {
            AddCommandOverride(sName, Override_Command,      ReadFlagString(sFlags));
        }
        else if (StrEqual(sType, "group")) {
            AddCommandOverride(sName, Override_CommandGroup, ReadFlagString(sFlags));
        }
    }

    // Clear the sequence so another connect doesn't refetch
    g_iRebuildCachePart[AdminCache_Overrides] = 0;
}


/**
 * Natives
 */
public int Native_GetAdminId(Handle plugin, int numParams)
{
    int iClient = GetNativeCell(1);
    return iClient > 0 && IsClientInGame(iClient) ? g_iAdminId[iClient] : 0;
}

public int Native_AddAdmin(Handle plugin, int numParams)
{
    // order = client, name, authtype, identity, password, groups

    char sGroups[512], sIdentity[65], sName[33], sPassword[65], sType[16];
    int iClient = GetNativeCell(1);
    GetNativeString(2, sName,     sizeof(sName));
    GetNativeString(3, sType,     sizeof(sType));
    GetNativeString(4, sIdentity, sizeof(sIdentity));
    GetNativeString(5, sPassword, sizeof(sPassword));
    GetNativeString(6, sGroups,   sizeof(sGroups));

    if (!StrEqual(sType, AUTHMETHOD_STEAM) && !StrEqual(sType, AUTHMETHOD_IP) && !StrEqual(sType, AUTHMETHOD_NAME)) {
        return ThrowNativeError(SP_ERROR_NATIVE, "%s%T", SB_PREFIX, "Invalid authtype", iClient);
    }

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(iClient));
    hPack.WriteString(sName);
    hPack.WriteString(sType);
    hPack.WriteString(sIdentity);
    hPack.WriteString(sPassword);
    hPack.WriteString(sGroups);

    char sEscapedIdentity[129], sQuery[1024];
    SB_Escape(sIdentity, sEscapedIdentity, sizeof(sEscapedIdentity));
    Format(sQuery, sizeof(sQuery), "SELECT 1 \
                                    FROM   {{admins}} \
                                    WHERE  auth     = '%s' \
                                      AND  identity = '%s'",
                                    sType, sEscapedIdentity);
    SB_Query(Query_AddAdmin, sQuery, hPack);

    return SP_ERROR_NONE;
}

public int Native_DeleteAdmin(Handle plugin, int numParams)
{
    // order = client, authtype, identity

    char sIdentity[65], sType[16];
    int iClient = GetNativeCell(1);
    GetNativeString(2, sType,     sizeof(sType));
    GetNativeString(3, sIdentity, sizeof(sIdentity));

    if (!StrEqual(sType, AUTHMETHOD_STEAM) && !StrEqual(sType, AUTHMETHOD_IP) && !StrEqual(sType, AUTHMETHOD_NAME)) {
        return ThrowNativeError(SP_ERROR_NATIVE, "%s%T", SB_PREFIX, "Invalid authtype", iClient);
    }

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(iClient));
    hPack.WriteString(sType);
    hPack.WriteString(sIdentity);

    char sEscapedIdentity[129], sQuery[1024];
    SB_Escape(sIdentity, sEscapedIdentity, sizeof(sEscapedIdentity));
    Format(sQuery, sizeof(sQuery), "SELECT id \
                                    FROM   {{admins}} \
                                    WHERE  auth     = '%s' \
                                      AND  identity = '%s'",
                                    sType, sEscapedIdentity);
    SB_Query(Query_DelAdmin, sQuery, hPack);

    return SP_ERROR_NONE;
}

public int Native_AddGroup(Handle plugin, int numParams)
{
    // order = client, name, flags, immunity

    char sFlags[33], sName[33];
    int iClient   = GetNativeCell(1);
    GetNativeString(2, sName,  sizeof(sName));
    GetNativeString(3, sFlags, sizeof(sFlags));
    int iImmunity = GetNativeCell(4);

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(iClient));
    hPack.WriteString(sName);
    hPack.WriteString(sFlags);
    hPack.WriteCell(iImmunity);

    char sEscapedName[65], sQuery[1024];
    SB_Escape(sName, sEscapedName, sizeof(sEscapedName));
    Format(sQuery, sizeof(sQuery), "SELECT 1 \
                                    FROM   {{server_groups}} \
                                    WHERE  name = '%s'",
                                    sEscapedName);
    SB_Query(Query_AddGroup, sQuery, hPack);
}

public int Native_DeleteGroup(Handle plugin, int numParams)
{
    // order = client, name

    char sName[33];
    int iClient = GetNativeCell(1);
    GetNativeString(2, sName, sizeof(sName));

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(iClient));
    hPack.WriteString(sName);

    char sEscapedName[65], sQuery[1024];
    SB_Escape(sName, sEscapedName, sizeof(sEscapedName));
    Format(sQuery, sizeof(sQuery), "SELECT id \
                                    FROM   {{server_groups}} \
                                    WHERE  name = '%s'",
                                    sEscapedName);
    SB_Query(Query_DelGroup, sQuery, hPack);
}

public int Native_SetAdminGroups(Handle plugin, int numParams)
{
    // order = client, authtype, identity, groups

    char sGroups[256], sIdentity[65], sType[16];
    int iClient = GetNativeCell(1);
    GetNativeString(2, sType,     sizeof(sType));
    GetNativeString(3, sIdentity, sizeof(sIdentity));
    GetNativeString(4, sGroups,   sizeof(sGroups));
    TrimString(sGroups);

    if (!StrEqual(sType, AUTHMETHOD_STEAM) && !StrEqual(sType, AUTHMETHOD_IP) && !StrEqual(sType, AUTHMETHOD_NAME)) {
        return ThrowNativeError(SP_ERROR_NATIVE, "%s%T", SB_PREFIX, "Invalid authtype", iClient);
    }

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(iClient));

    // If groups were passed
    if (sGroups[0]) {
        /**
         * Get the total number of groups.
         * We have to do this first because the query needs to know
         * the amount before it starts to read the group names.
         */
        char sName[33];
        int iIndex  = 0;
        ArrayList hGroups = new ArrayList(33);
        while (iIndex != -1) {
            iIndex = BreakString(sGroups[iIndex], sName, sizeof(sName));
            hGroups.PushString(sName);
        }

        // Store amount of passed groups
        int iGroups = GetArraySize(hGroups);
        hPack.WriteCell(iGroups);

        // Store group names
        for (int i = 0; i < iGroups; i++) {
            hGroups.GetString(i, sName, sizeof(sName));
            hPack.WriteString(sName);
        }
    } else {
        hPack.WriteCell(0);
    }

    char sEscapedIdentity[129], sQuery[1024];
    SB_Escape(sIdentity, sEscapedIdentity, sizeof(sEscapedIdentity));
    Format(sQuery, sizeof(sQuery), "SELECT id \
                                    FROM   {{admins}} \
                                    WHERE  auth     = '%s' \
                                      AND  identity = '%s'",
                                    sType, sEscapedIdentity);
    SB_Query(Query_SetAdminGroups, sQuery, hPack);

    return SP_ERROR_NONE;
}


/**
 * Stocks
 */
void FetchAdmin(int iClient)
{
    char sAuth[20], sIp[16], sName[MAX_NAME_LENGTH + 1];
    // Get authentication information from the client.
    GetClientName(iClient, sName, sizeof(sName));
    GetClientIP(iClient,   sIp,   sizeof(sIp));

    if (!GetClientAuthId(iClient, AuthId_Steam2, sAuth, sizeof(sAuth)) || StrContains("BOT STEAM_ID_LAN", sAuth) != -1) {
        sAuth[8] = '\0';
    }

    // Construct the query using the information the client gave us.
    char sCondition[1024] = "", sEscapedName[MAX_NAME_LENGTH * 2 + 1], sQuery[1024];
    if (g_bRequireSiteLogin) {
        StrCat(sCondition, sizeof(sCondition), " AND ad.login_time IS NOT NULL");
    }

    SB_Escape(sName, sEscapedName, sizeof(sEscapedName));
    Format(sQuery, sizeof(sQuery), "SELECT    ad.id, ad.name, ad.auth, ad.identity, ad.server_password, COUNT(ag.group_id) \
                                    FROM      {{admins}}                AS ad \
                                    LEFT JOIN {{admins_server_groups}}  AS ag ON ag.admin_id = ad.id \
                                    LEFT JOIN {{servers_server_groups}} AS gs ON gs.group_id = ag.group_id \
                                    WHERE     ((ad.auth = '%s' AND ad.identity REGEXP '^(STEAM_[0-9]:%s)$') \
                                       OR      (ad.auth = '%s' AND '%s' REGEXP REPLACE(REPLACE(ad.identity, '.', '\\.') , '.0', '..{1,3}')) \
                                       OR      (ad.auth = '%s' AND ad.identity = '%s')) \
                                      AND     gs.server_id = %i%s \
                                    GROUP BY  ad.id",
                                    AUTHMETHOD_STEAM, sAuth[8], AUTHMETHOD_IP, sIp, AUTHMETHOD_NAME, sEscapedName, g_iServerId, sCondition);

    // Send the actual query.
    g_iPlayerSeq[iClient] = ++g_iSequence;

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(iClient));
    hPack.WriteCell(g_iPlayerSeq[iClient]);
    hPack.WriteString(sQuery);

    #if defined _DEBUG
    PrintToServer("%sSending admin query: %s", SB_PREFIX, sQuery);
    #endif

    SB_Query(Query_SelectAdmin, sQuery, hPack, DBPrio_High);
}

void FetchAdmins()
{
    for (int i = 1; i <= MaxClients; i++) {
        if (g_bPlayerAuth[i] && GetUserAdmin(i) == INVALID_ADMIN_ID) {
            FetchAdmin(i);
        }
    }

    // This round of updates is done.  Go in peace.
    g_iRebuildCachePart[AdminCache_Admins] = 0;
}

void FetchGroups(int iSequence)
{
    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "SELECT    sg.name, sg.flags, sg.immunity \
                                    FROM      {{server_groups}}         AS sg \
                                    LEFT JOIN {{servers_server_groups}} AS gs ON gs.group_id = sg.id \
                                    WHERE     gs.server_id = %i",
                                    g_iServerId);

    DataPack hPack = new DataPack();
    hPack.WriteCell(iSequence);
    hPack.WriteString(sQuery);

    #if defined _DEBUG
    PrintToServer("%sSending groups query: %s", SB_PREFIX, sQuery);
    #endif

    SB_Query(Query_SelectGroups, sQuery, hPack, DBPrio_High);
}

void FetchOverrides(int iSequence)
{
    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "SELECT type, name, flags \
                                    FROM   {{overrides}}");

    DataPack hPack = new DataPack();
    hPack.WriteCell(iSequence);
    hPack.WriteString(sQuery);

    #if defined _DEBUG
    PrintToServer("%sSending overrides query: %s", SB_PREFIX, sQuery);
    #endif

    SB_Query(Query_SelectOverrides, sQuery, hPack, DBPrio_High);
}
