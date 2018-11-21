#include <sourcemod>
#include <sourcebans>
#include <sb_bans>
#undef REQUIRE_PLUGIN
#include <sb_admins>

#pragma newdecls required
#pragma semicolon 1

#define BAN_TYPE_STEAM 0
#define BAN_TYPE_IP 1
#define BAN_LENGTH_PERMANENT 0

//#define _DEBUG

public Plugin myinfo =
{
    name        = "SourceBans: Bans",
    author      = "GameConnect",
    description = "Advanced ban management for the Source engine",
    version     = SB_VERSION,
    url         = "http://www.sourcebans.net"
};


/**
 * Globals
 */
bool g_EnableAdmins;
bool g_PlayerStatus[MAXPLAYERS + 1];
float g_RetryTime;
int g_ProcessQueueTime;
int g_ServerId;
char g_Website[256];
Database g_SQLiteDB;
Handle g_PlayerRecheck[MAXPLAYERS + 1];
Handle g_ProcessQueue;


/**
 * Plugin Forwards
 */
public APLRes AskPluginLoad2(Handle myself, bool late, char[] error, int err_max)
{
    CreateNative("SB_ReportPlayer", Native_ReportPlayer);
    RegPluginLibrary("sb_bans");

    return APLRes_Success;
}

public void OnPluginStart()
{
    LoadTranslations("common.phrases");
    LoadTranslations("sourcebans.phrases");

    // Hook player_connect event to prevent connection spamming from banned players
    HookEvent("player_connect", Event_PlayerConnect, EventHookMode_Pre);

    g_EnableAdmins = LibraryExists("sb_admins");

    // Account for late loading
    if (LibraryExists("sourcebans")) {
        SB_Init();
    }

    // Connect to local database
    char error[256];
    g_SQLiteDB = SQLite_UseDatabase("sourcemod-local", error, sizeof(error));
    if (error[0]) {
        LogError("%T (%s)", "Could not connect to database", LANG_SERVER, error);
        return;
    }

    // Create local bans table
    SQL_FastQuery(g_SQLiteDB, "CREATE TABLE IF NOT EXISTS sb_bans (admin_id INTEGER, type INTEGER, steam TEXT PRIMARY KEY ON CONFLICT REPLACE, ip TEXT, name TEXT, reason TEXT, length INTEGER, admin_ip TEXT, create_time TEXT, insert_time TEXT, queued BOOLEAN)");

    // Process temporary bans every minute
    CreateTimer(60.0, Timer_ProcessTemp, _, TIMER_REPEAT);
}

public void OnLibraryAdded(const char[] name)
{
    if (StrEqual(name, "sb_admins")) {
        g_EnableAdmins = true;
    }
}

public void OnLibraryRemoved(const char[] name)
{
    if (StrEqual(name, "sb_admins")) {
        g_EnableAdmins = false;
    }
}


/**
 * SourceBans Forwards
 */
public void SB_OnInit()
{
    g_ProcessQueueTime = SB_GetConfigInt("ProcessQueueTime");
    g_RetryTime        = SB_GetConfigFloat("RetryTime");
    g_ServerId         = SB_GetServerId();

    SB_GetConfigString("Website", g_Website, sizeof(g_Website));

    // Restart process queue timer
    delete g_ProcessQueue;

    g_ProcessQueue = CreateTimer(g_ProcessQueueTime * 60.0, Timer_ProcessQueue, _, TIMER_REPEAT);
}


/**
 * Ban Forwards
 */
public Action OnBanClient(int client, int time, int flags, const char[] reason, const char[] kick_message, const char[] command, any admin)
{
    int adminId = GetAdminId(admin),
        banType = (flags & BANFLAG_IP) ? BAN_TYPE_IP : BAN_TYPE_STEAM;
    char adminIp[16], ip[16], name[MAX_NAME_LENGTH + 1], steam[20];
    GetClientAuthId(client, AuthId_Steam3, steam, sizeof(steam));
    GetClientIP(client,   ip,   sizeof(ip));
    GetClientName(client, name, sizeof(name));

    if (admin) {
        GetClientIP(admin, adminIp, sizeof(adminIp));
    }
    if (!SB_IsConnected()) {
        InsertLocalBan(adminId, banType, steam, ip, name, reason, time, adminIp, "now", true);
        return Plugin_Handled;
    }

    DataPack pack = new DataPack();
    pack.WriteCell(adminId);
    pack.WriteCell(banType);
    pack.WriteString(steam);
    pack.WriteString(ip);
    pack.WriteString(name);
    pack.WriteString(reason);
    pack.WriteCell(time);
    pack.WriteString(adminIp);

    char escapedName[MAX_NAME_LENGTH * 2 + 1], escapedReason[512], query[1024];
    SB_Escape(name,   escapedName,   sizeof(escapedName));
    SB_Escape(reason, escapedReason, sizeof(escapedReason));
    Format(query, sizeof(query), "INSERT INTO sb_bans (admin_id, server_id, type, steam, ip, name, reason, length, admin_ip, create_time) \
                                  VALUES (NULLIF(%i, 0), %i, %i, '%s', '%s', '%s', '%s', %i, '%s', NOW())",
                                  adminId, g_ServerId, banType, steam, ip, escapedName, escapedReason, time, adminIp);

    SB_Query(Query_BanClient, query, pack, DBPrio_High);
    return Plugin_Handled;
}

public Action OnBanIdentity(const char[] identity, int time, int flags, const char[] reason, const char[] command, any admin)
{
    int adminId = GetAdminId(admin),
        banType = (flags & BANFLAG_IP) ? BAN_TYPE_IP : BAN_TYPE_STEAM;
    char adminIp[16];

    if (admin) {
        GetClientIP(admin, adminIp, sizeof(adminIp));
    }
    if (!SB_IsConnected()) {
        if (flags & BANFLAG_IP) {
            InsertLocalBan(adminId, banType, "", identity, "", reason, time, adminIp, "now", true);
        } else {
            InsertLocalBan(adminId, banType, identity, "", "", reason, time, adminIp, "now", true);
        }
        return Plugin_Handled;
    }

    DataPack pack = new DataPack();
    pack.WriteCell(adminId);
    pack.WriteCell(banType);
    pack.WriteString(identity);
    pack.WriteString(reason);
    pack.WriteCell(time);
    pack.WriteString(adminIp);

    char escapedIdentity[64], escapedReason[512], query[1024];
    SB_Escape(identity, escapedIdentity, sizeof(escapedIdentity));
    SB_Escape(reason,   escapedReason,   sizeof(escapedReason));

    if (flags & BANFLAG_IP) {
        Format(query, sizeof(query), "INSERT INTO sb_bans (admin_id, server_id, type, ip, reason, length, admin_ip, create_time) \
                                      VALUES (NULLIF(%i, 0), %i, %i, '%s', '%s', %i, '%s', NOW())",
                                      adminId, g_ServerId, banType, escapedIdentity, escapedReason, time, adminIp);
    } else {
        Format(query, sizeof(query), "INSERT INTO sb_bans (admin_id, server_id, type, steam, reason, length, admin_ip, create_time) \
                                      VALUES (NULLIF(%i, 0), %i, %i, '%s', '%s', %i, '%s', NOW())",
                                      adminId, g_ServerId, banType, escapedIdentity, escapedReason, time, adminIp);
    }

    SB_Query(Query_BanIdentity, query, pack, DBPrio_High);
    return Plugin_Handled;
}

public Action OnRemoveBan(const char[] identity, int flags, const char[] command, any admin)
{
    int adminId = GetAdminId(admin);

    char escapedIdentity[64], query[512];
    SB_Escape(identity, escapedIdentity, sizeof(escapedIdentity));

    if (flags & BANFLAG_IP) {
        Format(query, sizeof(query), "UPDATE sb_bans \
                                      SET    unban_admin_id = %i, \
                                             unban_time     = NOW() \
                                      WHERE  type           = %i \
                                        AND  ip             = '%s' \
                                        AND  (length = %i OR DATE_ADD(create_time, INTERVAL length MINUTE) > NOW()) \
                                        AND  unban_time IS NULL",
                                      adminId, BAN_TYPE_IP, escapedIdentity, BAN_LENGTH_PERMANENT);
    } else {
        Format(query, sizeof(query), "UPDATE sb_bans \
                                      SET    unban_admin_id = %i, \
                                             unban_time     = NOW() \
                                      WHERE  type           = %i \
                                        AND  steam          = '%s' \
                                        AND  (length = %i OR DATE_ADD(create_time, INTERVAL length MINUTE) > NOW()) \
                                        AND  unban_time IS NULL",
                                      adminId, BAN_TYPE_STEAM, escapedIdentity, BAN_LENGTH_PERMANENT);
    }

    SB_Execute(query);
    DeleteLocalBan(identity);
    return Plugin_Handled;
}


/**
 * Client Forwards
 */
public void OnClientDisconnect(int client)
{
    delete g_PlayerRecheck[client];

    g_PlayerStatus[client] = false;
}

public void OnClientPostAdminCheck(int client)
{
    char ip[16], steam[20];
    GetClientAuthId(client, AuthId_Steam3, steam, sizeof(steam));
    GetClientIP(client, ip, sizeof(ip));

    if (HasLocalBan(steam, ip)) {
        KickClient(client, "%t", "Banned Check Site", g_Website);
        return;
    }
    if (!SB_IsConnected() || StrEqual(steam, "BOT") || StrEqual(steam, "STEAM_ID_LAN")) {
        g_PlayerStatus[client] = true;
        return;
    }

    char query[1024];
    Format(query, sizeof(query), "SELECT id, admin_id, type, steam, ip, name, reason, length, admin_ip, create_time \
                                  FROM   sb_bans \
                                  WHERE  ((type = %i AND steam REGEXP '^\\\\[U:[0-4]:%s$') \
                                          OR (type = %i AND '%s' REGEXP REPLACE(REPLACE(ip, '.', '\\\\.'), '.0', '..{1,3}'))) \
                                    AND  (length = %i OR DATE_ADD(create_time, INTERVAL length MINUTE) > NOW()) \
                                    AND  unban_time IS NULL",
                                  BAN_TYPE_STEAM, steam[5], BAN_TYPE_IP, ip, BAN_LENGTH_PERMANENT);

    SB_Query(Query_VerifyPlayer, query, GetClientSerial(client), DBPrio_High);
}


/**
 * Event Callbacks
 */
public Action Event_PlayerConnect(Event event, const char[] name, bool dontBroadcast)
{
    char ip[16];
    event.GetString("address", ip, sizeof(ip));

    // Strip the port
    int pos = StrContains(ip, ":");
    if (pos != -1) {
        ip[pos] = '\0';
    }

    // If the IP address is banned, don't broadcast the event
    if (HasLocalBan("", ip)) {
        event.BroadcastDisabled = true;
    }

    return Plugin_Continue;
}


/**
 * Query Callbacks
 */
public void Query_AddedFromQueue(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    char identity[20];
    pack.Reset();
    pack.ReadString(identity, sizeof(identity));
    delete pack;

    if (!error[0]) {
        DeleteLocalBan(identity);
    }
}

public void Query_BanClient(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char adminIp[16], ip[16], name[MAX_NAME_LENGTH + 1], reason[128], steam[20];
    int adminId = pack.ReadCell(),
        banType = pack.ReadCell();
    pack.ReadString(steam,   sizeof(steam));
    pack.ReadString(ip,      sizeof(ip));
    pack.ReadString(name,    sizeof(name));
    pack.ReadString(reason,  sizeof(reason));
    int length  = pack.ReadCell();
    pack.ReadString(adminIp, sizeof(adminIp));
    delete pack;

    InsertLocalBan(adminId, banType, steam, ip, name, reason, length, adminIp, "now", !!error[0]);
}

public void Query_BanIdentity(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char adminIp[16], identity[20], reason[128];
    int adminId = pack.ReadCell(),
        banType = pack.ReadCell();
    pack.ReadString(identity, sizeof(identity));
    pack.ReadString(reason,   sizeof(reason));
    int length  = pack.ReadCell();
    pack.ReadString(adminIp, sizeof(adminIp));
    delete pack;

    if (banType == BAN_TYPE_IP) {
        InsertLocalBan(adminId, banType, "", identity, "", reason, length, adminIp, "now", !!error[0]);
    } else {
        InsertLocalBan(adminId, banType, identity, "", "", reason, length, adminIp, "now", !!error[0]);
    }
}

public void Query_VerifyPlayer(Database db, DBResultSet results, const char[] error, any serial)
{
    int client = GetClientFromSerial(serial);
    if (!client) {
        return;
    }
    if (error[0]) {
        LogError("Failed to verify the player: %s", error);

        g_PlayerRecheck[client] = CreateTimer(g_RetryTime, Timer_PlayerRecheck, serial);
        return;
    }
    if (!results.FetchRow()) {
        g_PlayerStatus[client] = true;
        return;
    }

    // SELECT id, admin_id, type, steam, ip, name, reason, length, admin_ip, create_time
    char adminIp[16], createTime[20], ip[16], name[MAX_NAME_LENGTH + 1], reason[256], steam[20];
    int banId   = results.FetchInt(0),
        adminId = results.FetchInt(1),
        banType = results.FetchInt(2);
    results.FetchString(3, steam,      sizeof(steam));
    results.FetchString(4, ip,         sizeof(ip));
    results.FetchString(5, name,       sizeof(name));
    results.FetchString(6, reason,     sizeof(reason));
    int length  = results.FetchInt(7);
    results.FetchString(8, adminIp,    sizeof(adminIp));
    results.FetchString(9, createTime, sizeof(createTime));

    char escapedName[MAX_NAME_LENGTH * 2 + 1], query[1024];
    SB_Escape(name, escapedName, sizeof(escapedName));
    Format(query, sizeof(query), "INSERT INTO sb_blocks (ban_id, server_id, name, create_time) \
                                  VALUES (%i, %i, '%s', NOW())",
                                  banId, g_ServerId, escapedName);

    SB_Execute(query, DBPrio_High);
    InsertLocalBan(adminId, banType, steam, ip, name, reason, length, adminIp, createTime);

    KickClient(client, "%t", "Banned Check Site", g_Website);
}


/**
 * Timer Callbacks
 */
public Action Timer_PlayerRecheck(Handle timer, any serial)
{
    int client = GetClientFromSerial(serial);
    if (!client) {
        return;
    }

    if (!g_PlayerStatus[client] && IsClientInGame(client) && IsClientAuthorized(client)) {
        OnClientPostAdminCheck(client);
    }

    g_PlayerRecheck[client] = null;
}

public Action Timer_ProcessQueue(Handle timer, any data)
{
    if (!g_SQLiteDB) {
        return;
    }

    DBResultSet results = SQL_Query(g_SQLiteDB, "SELECT admin_id, type, steam, ip, name, reason, length, admin_ip, create_time \
                                                 FROM   sb_bans \
                                                 WHERE  queued = 1");
    if (!results) {
        return;
    }

    int adminId, banType, length;
    char adminIp[16], createTime[20], ip[16], name[MAX_NAME_LENGTH + 1], reason[256], steam[20];
    while (results.FetchRow()) {
        adminId = results.FetchInt(0);
        banType = results.FetchInt(1);
        results.FetchString(2, steam,      sizeof(steam));
        results.FetchString(3, ip,         sizeof(ip));
        results.FetchString(4, name,       sizeof(name));
        results.FetchString(5, reason,     sizeof(reason));
        length  = results.FetchInt(6);
        results.FetchString(7, adminIp,    sizeof(adminIp));
        results.FetchString(8, createTime, sizeof(createTime));

        DataPack pack = new DataPack();
        pack.WriteString(banType == BAN_TYPE_IP ? ip : steam);

        char escapedName[MAX_NAME_LENGTH * 2 + 1], escapedReason[256], query[1024];
        SB_Escape(name,   escapedName,   sizeof(escapedName));
        SB_Escape(reason, escapedReason, sizeof(escapedReason));
        Format(query, sizeof(query), "INSERT INTO sb_bans (admin_id, server_id, type, steam, ip, name, reason, length, admin_ip, create_time) \
                                      VALUES (NULLIF(%i, 0), %i, %i, NULLIF('%s', ''), NULLIF('%s', ''), NULLIF('%s', ''), '%s', %i, '%s', '%s')",
                                      adminId, g_ServerId, banType, steam, ip, escapedName, escapedReason, length, adminIp, createTime);

        SB_Query(Query_AddedFromQueue, query, pack);
    }

    delete results;
}

public Action Timer_ProcessTemp(Handle timer, any data)
{
    if (!g_SQLiteDB) {
        return;
    }

    // Delete temporary bans that have expired or were added over 5 minutes ago
    char query[512];
    g_SQLiteDB.Format(query, sizeof(query), "DELETE FROM sb_bans \
                                             WHERE  queued = 0 \
                                               AND  (DATETIME(create_time, '+' || length || ' minutes') <= DATETIME() \
                                                     OR DATETIME(insert_time, '+5 minutes') <= DATETIME())");
    SQL_FastQuery(g_SQLiteDB, query);
}


/**
 * Natives
 */
public int Native_ReportPlayer(Handle plugin, int numParams)
{
    int client = GetNativeCell(1),
        target = GetNativeCell(2);
    char reason[256];
    GetNativeString(3, reason, sizeof(reason));

    char ip[16], name[MAX_NAME_LENGTH + 1], targetIp[16], targetName[MAX_NAME_LENGTH + 1], targetSteam[20];
    GetClientAuthId(target, AuthId_Steam3, targetSteam, sizeof(targetSteam));
    GetClientIP(client,   ip,         sizeof(ip));
    GetClientIP(target,   targetIp,   sizeof(targetIp));
    GetClientName(client, name,       sizeof(name));
    GetClientName(target, targetName, sizeof(targetName));

    char escapedName[MAX_NAME_LENGTH * 2 + 1], escapedReason[512], escapedTargetName[MAX_NAME_LENGTH * 2 + 1], query[1024];
    SB_Escape(name,       escapedName,       sizeof(escapedName));
    SB_Escape(reason,     escapedReason,     sizeof(escapedReason));
    SB_Escape(targetName, escapedTargetName, sizeof(escapedTargetName));
    Format(query, sizeof(query), "INSERT INTO sb_reports (server_id, name, steam, ip, reason, user_name, user_ip, create_time) \
                                  VALUES (%i, '%s', '%s', '%s', '%s', '%s', '%s', NOW())",
                                  g_ServerId, escapedTargetName, targetSteam, targetIp, escapedReason, escapedName, ip);

    SB_Execute(query);
}


/**
 * Stocks
 */
void DeleteLocalBan(const char[] identity)
{
    if (!g_SQLiteDB) {
        return;
    }

    char query[256];
    g_SQLiteDB.Format(query, sizeof(query), "DELETE FROM sb_bans \
                                             WHERE  (type = %i AND steam = '%s') \
                                                OR  (type = %i AND ip    = '%s')",
                                             BAN_TYPE_STEAM, identity, BAN_TYPE_IP, identity);
    SQL_FastQuery(g_SQLiteDB, query);
}

int GetAdminId(int client)
{
    if (!g_EnableAdmins) {
        return 0;
    }

    return SB_GetAdminId(client);
}

bool HasLocalBan(const char[] steam, const char[] ip)
{
    if (!g_SQLiteDB) {
        return false;
    }

    char query[1024];
    g_SQLiteDB.Format(query, sizeof(query), "SELECT 1 \
                                             FROM   sb_bans \
                                             WHERE  ((type = %i AND steam = '%s') OR (type = %i AND ip = '%s')) \
                                               AND  (length = %i OR DATETIME(create_time, '+' || length || ' minutes') > DATETIME() \
                                                     OR (queued = 0 AND DATETIME(insert_time, '+5 minutes') > DATETIME()))",
                                             BAN_TYPE_STEAM, steam, BAN_TYPE_IP, ip, BAN_LENGTH_PERMANENT);

    DBResultSet results = SQL_Query(g_SQLiteDB, query);
    if (!results) {
        return false;
    }

    int rowCount = results.RowCount;

    delete results;
    return rowCount > 0;
}

void InsertLocalBan(int adminId, int banType, const char[] steam, const char[] ip, const char[] name, const char[] reason, int length, const char[] adminIp, const char[] createTime, bool queued = false)
{
    char query[1024];
    g_SQLiteDB.Format(query, sizeof(query), "INSERT INTO sb_bans (admin_id, type, steam, ip, name, reason, length, admin_ip, create_time, insert_time, queued) \
                                             VALUES (%i, %i, '%s', '%s', '%s', '%s', %i, '%s', DATETIME('%s'), DATETIME(), %i)",
                                             adminId, banType, steam, ip, name, reason, length, adminIp, createTime, queued);
    SQL_FastQuery(g_SQLiteDB, query);

    #if defined _DEBUG
    PrintToServer("%sAdded local ban (%d,%d,%s,%s,%s,%s,%d,%s,%s,%d)", SB_PREFIX, adminId, banType, steam, ip, name, reason, length, adminIp, createTime, queued);
    #endif
}
