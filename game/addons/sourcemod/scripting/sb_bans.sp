/**
 * SourceBans Bans Plugin
 *
 * @author GameConnect
 * @version 2.0.0
 * @copyright SourceBans (C)2007-2016 GameConnect.net.  All rights reserved.
 * @package SourceBans
 * @link http://www.sourcebans.net
 */

#include <sourcemod>
#include <sourcebans>
#include <sb_bans>
#undef REQUIRE_PLUGIN
#include <sb_admins>
#include <adminmenu>
#include <dbi>
#include <geoip>

#pragma newdecls required
#pragma semicolon 1

#define STEAM_BAN_TYPE		0
#define IP_BAN_TYPE				1
#define DEFAULT_BAN_TYPE	STEAM_BAN_TYPE

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
int g_iBanTarget[MAXPLAYERS + 1];
int g_iBanTime[MAXPLAYERS + 1];
int g_iProcessQueueTime;
int g_iServerId;
bool g_bEnableAddBan;
bool g_bEnableUnban;
bool g_bIsWaitingForChatReason[MAXPLAYERS + 1];
bool g_bPlayerStatus[MAXPLAYERS + 1];
float g_flRetryTime;
ArrayList g_hBanTimes;
ArrayList g_hBanTimesFlags;
ArrayList g_hBanTimesLength;
Menu g_hHackingMenu;
Handle g_hPlayerRecheck[MAXPLAYERS + 1];
Handle g_hProcessQueue;
Menu g_hReasonMenu;
Database g_hSQLiteDB;
TopMenu g_hTopMenu;
char g_sServerIp[16];
char g_sWebsite[256];


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
    RegAdminCmd("sm_ban",    Command_Ban,    ADMFLAG_BAN,   "sm_ban <#userid|name> <minutes|0> [reason]");
    RegAdminCmd("sm_banip",  Command_BanIp,  ADMFLAG_BAN,   "sm_banip <ip|#userid|name> <time> [reason]");
    RegAdminCmd("sm_addban", Command_AddBan, ADMFLAG_RCON,  "sm_addban <time> <steamid> [reason]");
    RegAdminCmd("sm_unban",  Command_Unban,  ADMFLAG_UNBAN, "sm_unban <steamid|ip>");

    LoadTranslations("common.phrases");
    LoadTranslations("sourcebans.phrases");
    LoadTranslations("basebans.phrases");

    // Hook player_connect event to prevent connection spamming from people that are banned
    HookEvent("player_connect", Event_PlayerConnect, EventHookMode_Pre);

    g_hHackingMenu = new Menu(MenuHandler_Reason);
    g_hReasonMenu  = new Menu(MenuHandler_Reason);
    g_hHackingMenu.ExitBackButton = true;
    g_hReasonMenu.ExitBackButton  = true;

    // Account for late loading
    TopMenu hTopMenu;
    if (LibraryExists("adminmenu") && (hTopMenu = GetAdminTopMenu())) {
        OnAdminMenuReady(hTopMenu);
    }

    if (LibraryExists("sourcebans")) {
        SB_Init();
    }

    // Connect to local database
    char sError[256] = "";
    g_hSQLiteDB      = SQLite_UseDatabase("sourcemod-local", sError, sizeof(sError));
    if (sError[0]) {
        LogError("%T (%s)", "Could not connect to database", LANG_SERVER, sError);
        return;
    }

    // Create local bans table
    SQL_FastQuery(g_hSQLiteDB, "CREATE TABLE IF NOT EXISTS sb_bans (type INTEGER, steam TEXT PRIMARY KEY ON CONFLICT REPLACE, ip TEXT, name TEXT, reason TEXT, length INTEGER, admin_id INTEGER, admin_ip TEXT, queued BOOLEAN, create_time INTEGER, insert_time INTEGER)");

    // Process temporary bans every minute
    CreateTimer(60.0, Timer_ProcessTemp, _, TIMER_REPEAT);
}

public void OnAdminMenuReady(Handle topmenu)
{
    TopMenu hTopMenu = TopMenu.FromHandle(topmenu);

    // Block us from being called twice
    if (hTopMenu == g_hTopMenu) {
        return;
    }

    // Save the handle
    g_hTopMenu = hTopMenu;

    // Find the "Player Commands" category
    TopMenuObject iPlayerCommands = g_hTopMenu.FindCategory(ADMINMENU_PLAYERCOMMANDS);
    if (iPlayerCommands) {
        g_hTopMenu.AddItem("sm_ban", MenuHandler_Ban, iPlayerCommands, "sm_ban", ADMFLAG_BAN);
    }
}

public void OnConfigsExecuted()
{
    if (DisablePlugin("basebans")) {
        // Re-add "Ban player" option to admin menu
        TopMenu hTopMenu;
        if (LibraryExists("adminmenu") && (hTopMenu = GetAdminTopMenu())) {
            g_hTopMenu = null;
            OnAdminMenuReady(hTopMenu);
        }
    }
}


/**
 * Client Forwards
 */
public void OnClientDisconnect(int client)
{
    if (g_hPlayerRecheck[client]) {
        KillTimer(g_hPlayerRecheck[client]);
    }

    g_hPlayerRecheck[client]          = INVALID_HANDLE;
    g_bIsWaitingForChatReason[client] = false;
    g_bPlayerStatus[client]           = false;
}

public void OnClientPostAdminCheck(int client)
{
    char sAuth[20], sIp[16], sName[MAX_NAME_LENGTH + 1], sReason[128];
    int iLength, iTime;
    GetClientAuthId(client, AuthId_Steam2, sAuth, sizeof(sAuth));
    GetClientIP(client,                    sIp,   sizeof(sIp));

    if (GetLocalBan(true, sAuth, sIp, sAuth, sIp, sName, sReason, iLength, iTime)) {
        PrintBan(client, sAuth, sIp, sName, sReason, iLength, iTime);
        // Delay kick, otherwise ban information will not be printed to console
        CreateTimer(1.0, Timer_KickClient, GetClientUserId(client));
        return;
    }
    if (!SB_IsConnected() || StrContains("BOT STEAM_ID_LAN", sAuth) != -1) {
        g_bPlayerStatus[client] = true;
        return;
    }

    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "SELECT type, steam, ip, name, reason, length, admin_id, admin_ip, create_time \
                                    FROM   {{bans}} \
                                    WHERE  ((type = %i AND steam REGEXP '^STEAM_[0-9]:%s$') OR (type = %i AND '%s' REGEXP REPLACE(REPLACE(ip, '.', '\\.') , '.0', '..{1,3}'))) \
                                      AND  (length = 0 OR create_time + length * 60 > UNIX_TIMESTAMP()) \
                                      AND  unban_time IS NULL",
                                    STEAM_BAN_TYPE, sAuth[8], IP_BAN_TYPE, sIp);
    SB_Query(Query_BanVerify, sQuery, ParseClientSerial(client), DBPrio_High);
}


/**
 * Ban Forwards
 */
public Action OnBanClient(int client, int time, int flags, const char[] reason, const char[] kick_message, const char[] command, any admin)
{
    char sAdminIp[16], sAuth[20], sIp[16], sName[MAX_NAME_LENGTH + 1];
    int iAdminId = GetAdminId(admin), iType;
    bool bSteam  = GetClientAuthId(client, AuthId_Steam2, sAuth, sizeof(sAuth));
    GetClientIP(client,   sIp,   sizeof(sIp));
    GetClientName(client, sName, sizeof(sName));

    // Set type depending on passed flags
    if (flags      & BANFLAG_AUTHID || ((flags & BANFLAG_AUTO) && bSteam)) {
        iType = STEAM_BAN_TYPE;
    }
    else if (flags & BANFLAG_IP) {
        iType = IP_BAN_TYPE;
    }
    // If no valid flag was passed, block banning
    else {
        return Plugin_Handled;
    }

    if (admin) {
        GetClientIP(admin, sAdminIp, sizeof(sAdminIp));
    } else {
        sAdminIp = g_sServerIp;
    }
    if (!SB_IsConnected()) {
        InsertLocalBan(iType, sAuth, sIp, sName, reason, time, iAdminId, sAdminIp, GetTime(), true);
        return Plugin_Handled;
    }
    if (time) {
        if (reason[0]) {
            ShowActivity2(admin, SB_PREFIX, "%t", "Banned player reason",      sName, time, reason);
        } else {
            ShowActivity2(admin, SB_PREFIX, "%t", "Banned player",             sName, time);
        }
    } else {
        if (reason[0]) {
            ShowActivity2(admin, SB_PREFIX, "%t", "Permabanned player reason", sName, reason);
        } else {
            ShowActivity2(admin, SB_PREFIX, "%t", "Permabanned player",        sName);
        }
    }

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(admin));
    hPack.WriteCell(time);
    hPack.WriteString(sAuth);
    hPack.WriteString(sIp);
    hPack.WriteString(sName);
    hPack.WriteString(reason);
    hPack.WriteCell(iAdminId);
    hPack.WriteString(sAdminIp);

    char sEscapedName[MAX_NAME_LENGTH * 2 + 1], sEscapedReason[256], sQuery[1024];
    SB_Escape(sName,  sEscapedName,   sizeof(sEscapedName));
    SB_Escape(reason, sEscapedReason, sizeof(sEscapedReason));
    Format(sQuery, sizeof(sQuery), "INSERT INTO {{bans}} (type, steam, ip, name, reason, length, server_id, admin_id, admin_ip, create_time) \
                                    VALUES      (%i, '%s', '%s', '%s', '%s', %i, %i, NULLIF(%i, 0), '%s', UNIX_TIMESTAMP())",
                                    iType, sAuth, sIp, sEscapedName, sEscapedReason, time, g_iServerId, iAdminId, sAdminIp);
    SB_Query(Query_BanInsert, sQuery, hPack, DBPrio_High);

    LogAction(admin, client, "\"%L\" banned \"%L\" (minutes \"%i\") (reason \"%s\")", admin, client, time, reason);
    return Plugin_Handled;
}

public Action OnBanIdentity(const char[] identity, int time, int flags, const char[] reason, const char[] command, any admin)
{
    char sAdminIp[16], sQuery[1024];
    int iAdminId = GetAdminId(admin);
    bool bSteam  = strncmp(identity, "STEAM_", 6) == 0;

    if (admin) {
        GetClientIP(admin, sAdminIp, sizeof(sAdminIp));
    } else {
        sAdminIp = g_sServerIp;
    }
    if (!SB_IsConnected()) {
        if (bSteam) {
            InsertLocalBan(STEAM_BAN_TYPE, identity, "", "", reason, time, iAdminId, sAdminIp, GetTime(), true);
        } else {
            InsertLocalBan(IP_BAN_TYPE,    "", identity, "", reason, time, iAdminId, sAdminIp, GetTime(), true);
        }
        return Plugin_Handled;
    }

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(admin));
    hPack.WriteCell(time);
    hPack.WriteString(identity);
    hPack.WriteString(reason);
    hPack.WriteCell(iAdminId);
    hPack.WriteString(sAdminIp);

    if (flags      & BANFLAG_AUTHID || ((flags & BANFLAG_AUTO) && bSteam)) {
        Format(sQuery, sizeof(sQuery), "SELECT 1 \
                                        FROM   {{bans}} \
                                        WHERE  type  = %i \
                                          AND  steam REGEXP '^STEAM_[0-9]:%s$' \
                                          AND  (length = 0 OR create_time + length * 60 > UNIX_TIMESTAMP()) \
                                          AND  unban_time IS NULL",
                                        STEAM_BAN_TYPE, identity[8]);
        SB_Query(Query_AddBanSelect, sQuery, hPack, DBPrio_High);

        LogAction(admin, -1, "\"%L\" added ban (minutes \"%i\") (id \"%s\") (reason \"%s\")", admin, time, identity, reason);
    }
    else if (flags & BANFLAG_IP     || ((flags & BANFLAG_AUTO) && !bSteam)) {
        Format(sQuery, sizeof(sQuery), "SELECT 1 \
                                        FROM   {{bans}} \
                                        WHERE  type = %i \
                                          AND  ip   = '%s' \
                                          AND  (length = 0 OR create_time + length * 60 > UNIX_TIMESTAMP()) \
                                          AND  unban_time IS NULL",
                                        IP_BAN_TYPE, identity);
        SB_Query(Query_BanIpSelect,  sQuery, hPack, DBPrio_High);

        LogAction(admin, -1, "\"%L\" added ban (minutes \"%i\") (ip \"%s\") (reason \"%s\")", admin, time, identity, reason);
    }
    return Plugin_Handled;
}

public Action OnRemoveBan(const char[] identity, int flags, const char[] command, any admin)
{
    char sQuery[1024];
    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(admin));
    hPack.WriteString(identity);

    if (flags      & BANFLAG_AUTHID) {
        Format(sQuery, sizeof(sQuery), "SELECT 1 \
                                        FROM   {{bans}} \
                                        WHERE  type  = %i \
                                          AND  steam REGEXP '^STEAM_[0-9]:%s$' \
                                          AND  (length = 0 OR create_time + length * 60 > UNIX_TIMESTAMP()) \
                                          AND  unban_time IS NULL",
                                        STEAM_BAN_TYPE, identity[8]);
    }
    else if (flags & BANFLAG_IP) {
        Format(sQuery, sizeof(sQuery), "SELECT 1 \
                                        FROM   {{bans}} \
                                        WHERE  type = %i \
                                          AND  ip   = '%s' \
                                          AND  (length = 0 OR create_time + length * 60 > UNIX_TIMESTAMP()) \
                                          AND  unban_time IS NULL",
                                        IP_BAN_TYPE, identity);
    }
    SB_Query(Query_UnbanSelect, sQuery, hPack);

    LogAction(admin, -1, "\"%L\" removed ban (filter \"%s\")", admin, identity);
    return Plugin_Handled;
}


/**
 * SourceBans Forwards
 */
public void SB_OnConnect(Database db)
{
    g_iServerId = SB_GetConfigValue("ServerID");
}

public void SB_OnReload()
{
    // Get values from SourceBans config and store them locally
    SB_GetConfigString("ServerIP", g_sServerIp, sizeof(g_sServerIp));
    SB_GetConfigString("Website",  g_sWebsite,  sizeof(g_sWebsite));
    g_bEnableAddBan     = SB_GetConfigValue("Addban") == 1;
    g_bEnableUnban      = SB_GetConfigValue("Unban")  == 1;
    g_iProcessQueueTime = SB_GetConfigValue("ProcessQueueTime");
    g_flRetryTime       = SB_GetConfigValue("RetryTime");
    g_hBanTimes         = SB_GetConfigValue("BanTimes");
    g_hBanTimesFlags    = SB_GetConfigValue("BanTimesFlags");
    g_hBanTimesLength   = SB_GetConfigValue("BanTimesLength");

    // Get reasons from SourceBans config and store them locally
    char sReason[128];
    ArrayList hBanReasons     = SB_GetConfigValue("BanReasons");
    ArrayList hHackingReasons = SB_GetConfigValue("HackingReasons");

    // Empty reason menus
    g_hReasonMenu.RemoveAllItems();
    g_hHackingMenu.RemoveAllItems();

    // Add reasons from SourceBans config to reason menus
    for (int i = 0, iSize = hBanReasons.Length;     i < iSize; i++) {
        hBanReasons.GetString(i,     sReason, sizeof(sReason));
        g_hReasonMenu.AddItem(sReason,  sReason);
    }
    for (int i = 0, iSize = hHackingReasons.Length; i < iSize; i++) {
        hHackingReasons.GetString(i, sReason, sizeof(sReason));
        g_hHackingMenu.AddItem(sReason, sReason);
    }

    // Restart process queue timer
    if (g_hProcessQueue) {
        KillTimer(g_hProcessQueue);
    }

    g_hProcessQueue = CreateTimer(g_iProcessQueueTime * 60.0, Timer_ProcessQueue, _, TIMER_REPEAT);
}


/**
 * Commands
 */
public Action Command_Ban(int client, int args)
{
    if (args < 2) {
        ReplyToCommand(client, "%sUsage: sm_ban <#userid|name> <time|0> [reason]", SB_PREFIX);
        return Plugin_Handled;
    }

    int iLen;
    char sArg[256], sKickMessage[128], sTarget[64], sTime[12];
    GetCmdArgString(sArg, sizeof(sArg));
    iLen  = BreakString(sArg,       sTarget, sizeof(sTarget));
    iLen += BreakString(sArg[iLen], sTime,   sizeof(sTime));

    int iTarget = FindTarget(client, sTarget, true), iTime = StringToInt(sTime);
    if (iTarget == -1) {
        return Plugin_Handled;
    }

    if (!g_bPlayerStatus[iTarget]) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Ban Not Verified");
        return Plugin_Handled;
    }

    Format(sKickMessage, sizeof(sKickMessage), "%T", "Banned Check Site", iTarget, g_sWebsite);
    BanClient(iTarget, iTime, BANFLAG_AUTO, sArg[iLen], sKickMessage, "sm_ban", client);
    return Plugin_Handled;
}

public Action Command_BanIp(int client, int args)
{
    if (args < 2) {
        ReplyToCommand(client, "%sUsage: sm_banip <ip|#userid|name> <time> [reason]", SB_PREFIX);
        return Plugin_Handled;
    }

    int iLen, iTargets[1];
    bool tn_is_ml;
    char sArg[256], sIp[16], sTargets[MAX_TARGET_LENGTH], sTime[12];
    GetCmdArgString(sArg, sizeof(sArg));
    iLen  = BreakString(sArg,       sIp,   sizeof(sIp));
    iLen += BreakString(sArg[iLen], sTime, sizeof(sTime));

    if (StrEqual(sIp, "0")) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Cannot ban that IP");
        return Plugin_Handled;
    }

    int iTarget = -1, iTime = StringToInt(sTime);
    if (ProcessTargetString(
        sIp,
        client,
        iTargets,
        1,
        COMMAND_FILTER_CONNECTED|COMMAND_FILTER_NO_MULTI|COMMAND_FILTER_NO_BOTS,
        sTargets,
        sizeof(sTargets),
        tn_is_ml) > 0)
    {
        iTarget = iTargets[0];
        GetClientIP(iTarget, sIp, sizeof(sIp));
    }

    BanIdentity(sIp, iTime, BANFLAG_IP, sArg[iLen], "sm_banip",  client);
    return Plugin_Handled;
}

public Action Command_AddBan(int client, int args)
{
    if (args < 2) {
        ReplyToCommand(client, "%sUsage: sm_addban <time> <steamid> [reason]", SB_PREFIX);
        return Plugin_Handled;
    }
    if (!g_bEnableAddBan) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Can Not Add Ban", g_sWebsite);
        return Plugin_Handled;
    }

    int iLen;
    char sArg[256], sAuth[20], sTime[20];
    GetCmdArgString(sArg, sizeof(sArg));
    iLen  = BreakString(sArg,       sTime, sizeof(sTime));
    iLen += BreakString(sArg[iLen], sAuth, sizeof(sAuth));

    if (strncmp(sAuth, "STEAM_", 6) != 0 || sAuth[7] != ':') {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Invalid SteamID specified");
        return Plugin_Handled;
    }

    int iTime = StringToInt(sTime);

    BanIdentity(sAuth, iTime, BANFLAG_AUTHID, sArg[iLen], "sm_addban", client);
    return Plugin_Handled;
}

public Action Command_Unban(int client, int args)
{
    if (args < 1) {
        ReplyToCommand(client, "%sUsage: sm_unban <steamid|ip>", SB_PREFIX);
        return Plugin_Handled;
    }
    if (!g_bEnableUnban) {
        ReplyToCommand(client, "%s%t", SB_PREFIX, "Can Not Unban", g_sWebsite);
        return Plugin_Handled;
    }

    char sArg[24];
    GetCmdArgString(sArg, sizeof(sArg));
    StripQuotes(sArg);
    TrimString(sArg);

    RemoveBan(sArg, strncmp(sArg, "STEAM_", 6) == 0 && sArg[7] == ':' ? BANFLAG_AUTHID : BANFLAG_IP, "sm_unban", client);
    return Plugin_Handled;
}

public Action OnClientSayCommand(int client, const char[] command, const char[] sArgs)
{
    // If this client is not typing their own reason to ban someone, ignore
    if (!sArgs[0] || !g_bIsWaitingForChatReason[client]) {
        return Plugin_Continue;
    }

    g_bIsWaitingForChatReason[client] = false;

    if (StrEqual(sArgs[1], "abortban", false)) {
        PrintToChat(client, "%s%t", SB_PREFIX, "Chat Reason Aborted");
        return Plugin_Stop;
    }
    if (g_iBanTarget[client] == -1) {
        return Plugin_Continue;
    }

    char sKickMessage[128];
    Format(sKickMessage, sizeof(sKickMessage), "%T", "Banned Check Site", g_iBanTarget[client], g_sWebsite);
    BanClient(g_iBanTarget[client], g_iBanTime[client], BANFLAG_AUTO, sArgs, sKickMessage, "sm_ban", client);
    return Plugin_Stop;
}


/**
 * Events
 */
public Action Event_PlayerConnect(Handle event, const char[] name, bool dontBroadcast)
{
    char sIp[16];
    GetEventString(event, "address", sIp, sizeof(sIp));

    // Strip the port
    int iPos = StrContains(sIp, ":");
    if (iPos != -1) {
        sIp[iPos] = '\0';
    }

    // If the IP address is banned, don't broadcast the event
    if (GetLocalBan(false, "", sIp)) {
        SetEventBroadcast(event, true);
    }

    return Plugin_Continue;
}


/**
 * Timers
 */
public Action Timer_KickClient(Handle timer, any userid)
{
    int iClient = GetClientOfUserId(userid);
    if (!iClient) {
        return;
    }

    KickClient(iClient, "%t", "Banned Check Site", g_sWebsite);
}

public Action Timer_PlayerRecheck(Handle timer, any userid)
{
    int iClient = GetClientOfUserId(userid);
    if (!iClient) {
        return;
    }

    if (!g_bPlayerStatus[iClient] && IsClientInGame(iClient) && IsClientAuthorized(iClient)) {
        OnClientPostAdminCheck(iClient);
    }

    g_hPlayerRecheck[iClient] = INVALID_HANDLE;
}

public Action Timer_ProcessQueue(Handle timer, any data)
{
    if (!g_hSQLiteDB) {
        return;
    }

    DBResultSet hResults = SQL_Query(g_hSQLiteDB, "SELECT type, steam, ip, name, reason, length, admin_id, admin_ip, create_time \
                                                   FROM   sb_bans \
                                                   WHERE  queued = 1");
    if (!hResults) {
        return;
    }

    int iAdminId, iLength, iTime, iType;
    char sAdminIp[16], sAuth[20], sEscapedName[MAX_NAME_LENGTH * 2 + 1],
         sEscapedReason[256], sIp[16], sName[MAX_NAME_LENGTH + 1], sQuery[1024], sReason[128];
    while (hResults.FetchRow()) {
        iType    = hResults.FetchInt(0);
        hResults.FetchString(1, sAuth,    sizeof(sAuth));
        hResults.FetchString(2, sIp,      sizeof(sIp));
        hResults.FetchString(3, sName,    sizeof(sName));
        hResults.FetchString(4, sReason,  sizeof(sReason));
        iLength  = hResults.FetchInt(5);
        iAdminId = hResults.FetchInt(6);
        hResults.FetchString(7, sAdminIp, sizeof(sAdminIp));
        iTime    = hResults.FetchInt(8);

        if (iTime + iLength * 60 <= GetTime()) {
            DeleteLocalBan(iType == STEAM_BAN_TYPE ? sAuth : sIp);
            continue;
        }

        DataPack hPack = new DataPack();
        hPack.WriteString(iType == STEAM_BAN_TYPE ? sAuth : sIp);

        SB_Escape(sName,   sEscapedName,   sizeof(sEscapedName));
        SB_Escape(sReason, sEscapedReason, sizeof(sEscapedReason));
        Format(sQuery, sizeof(sQuery), "INSERT INTO {{bans}} (type, steam, ip, name, reason, length, server_id, admin_id, admin_ip, create_time) \
                                        VALUES      (%i, NULLIF('%s', ''), NULLIF('%s', ''), NULLIF('%s', ''), '%s', %i, %i, NULLIF(%i, 0), '%s', %i)",
                                        iType, sAuth, sIp, sEscapedName, sEscapedReason, iLength, g_iServerId, iAdminId, sAdminIp, iTime);
        SB_Query(Query_AddedFromQueue, sQuery, hPack);
    }

    delete hResults;
}

public Action Timer_ProcessTemp(Handle timer)
{
    if (!g_hSQLiteDB) {
        return;
    }

    // Delete temporary bans that expired or were added over 5 minutes ago
    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "DELETE FROM sb_bans \
                                    WHERE       queued = 0 \
                                      AND       (create_time + length * 60 <= %i OR insert_time + 300 <= %i)",
                                    GetTime(), GetTime());
    SQL_FastQuery(g_hSQLiteDB, sQuery);
}


/**
 * Menu Handlers
 */
public void MenuHandler_Ban(Handle topmenu, TopMenuAction action, TopMenuObject object_id, int param, char[] buffer, int maxlength)
{
    if (action      == TopMenuAction_DisplayOption) {
        Format(buffer, maxlength, "%T", "Ban player", param);
    }
    else if (action == TopMenuAction_SelectOption) {
        DisplayBanTargetMenu(param);
    }
}

public int MenuHandler_Target(Menu menu, MenuAction action, int param1, int param2)
{
    if (action      == MenuAction_Cancel) {
        if (param2 == MenuCancel_ExitBack && g_hTopMenu) {
            DisplayTopMenu(g_hTopMenu, param1, TopMenuPosition_LastCategory);
        }
    }
    else if (action == MenuAction_End) {
        delete menu;
    }
    else if (action == MenuAction_Select) {
        int iTarget;
        char sInfo[32];
        menu.GetItem(param2, sInfo, sizeof(sInfo));
        if (!(iTarget = GetClientOfUserId(StringToInt(sInfo)))) {
            PrintToChat(param1, "%s%t", SB_PREFIX, "Player no longer available");
        }
        else if (!CanUserTarget(param1, iTarget)) {
            PrintToChat(param1, "%s%t", SB_PREFIX, "Unable to target");
        }
        else {
            g_iBanTarget[param1] = iTarget;
            DisplayBanTimeMenu(param1);
        }
    }
}

public int MenuHandler_Time(Menu menu, MenuAction action, int param1, int param2)
{
    if (action      == MenuAction_Cancel) {
        if (param2 == MenuCancel_ExitBack) {
            DisplayBanTargetMenu(param1);
        }
    }
    else if (action == MenuAction_End) {
        delete menu;
    }
    else if (action == MenuAction_Select) {
        char sInfo[32];
        menu.GetItem(param2, sInfo, sizeof(sInfo));
        g_iBanTime[param1] = StringToInt(sInfo);
        g_hReasonMenu.Display(param1, MENU_TIME_FOREVER);
    }
}

public int MenuHandler_Reason(Menu menu, MenuAction action, int param1, int param2)
{
    if (action == MenuAction_Cancel && param2 == MenuCancel_ExitBack) {
        if (menu == g_hHackingMenu) {
            g_hReasonMenu.Display(param1, MENU_TIME_FOREVER);
        } else {
            DisplayBanTimeMenu(param1);
        }
    }
    if (action != MenuAction_Select) {
        return;
    }

    char sInfo[64];
    menu.GetItem(param2, sInfo, sizeof(sInfo));
    if (StrEqual(sInfo, "Hacking") && menu == g_hReasonMenu) {
        g_hHackingMenu.Display(param1, MENU_TIME_FOREVER);
        return;
    }
    if (StrEqual(sInfo, "Own Reason")) {
        g_bIsWaitingForChatReason[param1] = true;
        PrintToChat(param1, "%s%t", SB_PREFIX, "Chat Reason");
        return;
    }
    if (g_iBanTarget[param1] != -1) {
        char sKickMessage[128];
        Format(sKickMessage, sizeof(sKickMessage), "%T", "Banned Check Site", g_iBanTarget[param1], g_sWebsite);
        BanClient(g_iBanTarget[param1], g_iBanTime[param1], BANFLAG_AUTO, sInfo, sKickMessage, "sm_ban", param1);
    }

    g_iBanTarget[param1] = -1;
    g_iBanTime[param1]   = -1;
}


/**
 * Query Callbacks
 */
public void Query_BanInsert(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sAdminIp[16], sAuth[20], sIp[16], sName[MAX_NAME_LENGTH + 1], sReason[128];
    int iAdmin   = pack.ReadCell(),
        iLength  = pack.ReadCell();
    pack.ReadString(sAuth,    sizeof(sAuth));
    pack.ReadString(sIp,      sizeof(sIp));
    pack.ReadString(sName,    sizeof(sName));
    pack.ReadString(sReason,  sizeof(sReason));
    int iAdminId = pack.ReadCell();
    pack.ReadString(sAdminIp, sizeof(sAdminIp));
    delete pack;

    InsertLocalBan(STEAM_BAN_TYPE, sAuth, sIp, sName, sReason, iLength, iAdminId, sAdminIp, GetTime(), !!error[0]);
    if (error[0]) {
        LogError("Failed to insert the ban into the database: %s", error);

        if (ParseClientFromSerial(iAdmin, true)) {
            ReplyToCommand(iAdmin, "%sFailed to ban %s.", SB_PREFIX, sAuth);
        }
    }
}

public void Query_BanIpSelect(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sAdminIp[16], sEscapedReason[256], sIp[16], sQuery[1024], sReason[128];
    int iAdmin   = pack.ReadCell(),
        iLength  = pack.ReadCell();
    pack.ReadString(sIp,      sizeof(sIp));
    pack.ReadString(sReason,  sizeof(sReason));
    int iAdminId = pack.ReadCell();
    pack.ReadString(sAdminIp, sizeof(sAdminIp));

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to retrieve the IP ban from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to ban %s.",     SB_PREFIX, sIp);
        }

        delete pack;
        return;
    }
    if (results.RowCount) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%s%s is already banned.", SB_PREFIX, sIp);
        }

        delete pack;
        return;
    }

    SB_Escape(sReason, sEscapedReason, sizeof(sEscapedReason));
    Format(sQuery, sizeof(sQuery), "INSERT INTO {{bans}} (type, ip, reason, length, server_id, admin_id, admin_ip, create_time) \
                                    VALUES      (%i, '%s', '%s', %i, %i, NULLIF(%i, 0), '%s', UNIX_TIMESTAMP())",
                                    IP_BAN_TYPE, sIp, sEscapedReason, iLength, g_iServerId, iAdminId, sAdminIp);
    SB_Query(Query_BanIpInsert, sQuery, pack, DBPrio_High);
}

public void Query_BanIpInsert(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sAdminIp[30], sIp[16], sReason[128];
    int iAdmin   = pack.ReadCell(),
        iLength  = pack.ReadCell();
    pack.ReadString(sIp,      sizeof(sIp));
    pack.ReadString(sReason,  sizeof(sReason));
    int iAdminId = pack.ReadCell();
    pack.ReadString(sAdminIp, sizeof(sAdminIp));
    delete pack;

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    InsertLocalBan(IP_BAN_TYPE, "", sIp, "", sReason, iLength, iAdminId, sAdminIp, GetTime(), !!error[0]);
    if (error[0]) {
        LogError("Failed to insert the IP ban into the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to ban %s.", SB_PREFIX, sIp);
        }
        return;
    }
    if (bPrint) {
        ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "Ban added");
    }
}

public void Query_AddBanSelect(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sAdminIp[20], sAuth[20], sEscapedReason[256], sQuery[1024], sReason[128];
    int iAdmin   = pack.ReadCell(),
        iLength  = pack.ReadCell();
    pack.ReadString(sAuth,    sizeof(sAuth));
    pack.ReadString(sReason,  sizeof(sReason));
    int iAdminId = pack.ReadCell();
    pack.ReadString(sAdminIp, sizeof(sAdminIp));

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to retrieve the ID ban from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to ban %s.",     SB_PREFIX, sAuth);
        }

        delete pack;
        return;
    }
    if (results.RowCount) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%s%s is already banned.", SB_PREFIX, sAuth);
        }

        delete pack;
        return;
    }

    SB_Escape(sReason, sEscapedReason, sizeof(sEscapedReason));
    Format(sQuery, sizeof(sQuery), "INSERT INTO {{bans}} (type, steam, reason, length, server_id, admin_id, admin_ip, create_time) \
                                    VALUES      (%i, '%s', '%s', %i, %i, NULLIF(%i, 0), '%s', UNIX_TIMESTAMP())",
                                    STEAM_BAN_TYPE, sAuth, sEscapedReason, iLength, g_iServerId, iAdminId, sAdminIp);
    SB_Query(Query_AddBanInsert, sQuery, pack, DBPrio_High);
}

public void Query_AddBanInsert(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sAdminIp[20], sAuth[20], sReason[128];
    int iAdmin   = pack.ReadCell(),
        iLength  = pack.ReadCell();
    pack.ReadString(sAuth,    sizeof(sAuth));
    pack.ReadString(sReason,  sizeof(sReason));
    int iAdminId = pack.ReadCell();
    pack.ReadString(sAdminIp, sizeof(sAdminIp));
    delete pack;

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    InsertLocalBan(STEAM_BAN_TYPE, sAuth, "", "", sReason, iLength, iAdminId, sAdminIp, GetTime(), !!error[0]);
    if (error[0]) {
        LogError("Failed to insert the ID ban into the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to ban %s.", SB_PREFIX, sAuth);
        }
        return;
    }
    if (bPrint) {
        ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "Ban added");
    }
}

public void Query_UnbanSelect(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sIdentity[20], sQuery[1024];
    int iAdmin = pack.ReadCell();
    pack.ReadString(sIdentity, sizeof(sIdentity));

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to retrieve the ban from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to unban %s.",          SB_PREFIX, sIdentity);
        }

        delete pack;
        return;
    }
    if (!results.RowCount) {
        if (bPrint) {
            ReplyToCommand(iAdmin, "%sNo active bans found for %s.", SB_PREFIX, sIdentity);
        }

        delete pack;
        return;
    }

    if (strncmp(sIdentity, "STEAM_", 6) == 0) {
        Format(sQuery, sizeof(sQuery), "UPDATE   {{bans}} \
                                        SET      unban_admin_id = %i, \
                                                 unban_time     = UNIX_TIMESTAMP() \
                                        WHERE    type           = %i \
                                          AND    steam          REGEXP '^STEAM_[0-9]:%s$' \
                                        ORDER BY create_time DESC \
                                        LIMIT    1",
                                        GetAdminId(iAdmin), STEAM_BAN_TYPE, sIdentity[8]);
    } else {
        Format(sQuery, sizeof(sQuery), "UPDATE   {{bans}} \
                                        SET      unban_admin_id = %i, \
                                                 unban_time     = UNIX_TIMESTAMP() \
                                        WHERE    type           = %i \
                                          AND    ip             = '%s' \
                                        ORDER BY create_time DESC \
                                        LIMIT    1",
                                        GetAdminId(iAdmin), IP_BAN_TYPE, sIdentity);
    }

    SB_Query(Query_UnbanUpdate, sQuery, pack, DBPrio_High);

    DeleteLocalBan(sIdentity);
}

public void Query_UnbanUpdate(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    char sIdentity[20];
    int iAdmin = pack.ReadCell();
    pack.ReadString(sIdentity, sizeof(sIdentity));
    delete pack;

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to unban the ban from the database: %s", error);

        if (bPrint) {
            ReplyToCommand(iAdmin, "%sFailed to unban %s.", SB_PREFIX, sIdentity);
        }
        return;
    }
    if (bPrint) {
        ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "Removed bans matching", sIdentity);
    }
}

public void Query_ReportPlayer(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();

    int iAdmin  = pack.ReadCell(),
        iTarget = pack.ReadCell();
    delete pack;

    bool bPrint = ParseClientFromSerial(iAdmin, true);

    if (error[0]) {
        LogError("Failed to submit the ban to the database: %s", error);

        if (bPrint) {
            if (ParseClientFromSerial(iTarget)) {
                ReplyToCommand(iAdmin, "%sFailed to submit %N.", SB_PREFIX, iTarget);
            } else {
                ReplyToCommand(iAdmin, "%sFailed to submit.", SB_PREFIX);
            }
        }
        return;
    }
}

public void Query_BanVerify(Database db, DBResultSet results, const char[] error, any client)
{
    if (!ParseClientFromSerial(client)) {
        return;
    }

    if (error[0]) {
        LogError("Failed to verify the ban: %s", error);

        g_hPlayerRecheck[client] = CreateTimer(g_flRetryTime, Timer_PlayerRecheck, ParseClientSerial(client));
        return;
    }
    if (!results.FetchRow()) {
        g_bPlayerStatus[client] = true;
        return;
    }

    char sAdminIp[16], sAuth[20], sEscapedName[MAX_NAME_LENGTH * 2 + 1], sIp[16],
         sName[MAX_NAME_LENGTH + 1], sQuery[1024], sReason[128];
    GetClientAuthId(client, AuthId_Steam2, sAuth, sizeof(sAuth));
    GetClientIP(client,                    sIp,   sizeof(sIp));
    GetClientName(client,                  sName, sizeof(sName));

    SB_Escape(sName, sEscapedName, sizeof(sEscapedName));
    Format(sQuery, sizeof(sQuery), "INSERT INTO {{blocks}} (ban_id, name, server_id, create_time) \
                                    VALUES      ((SELECT id FROM {{bans}} WHERE ((type = %i AND steam REGEXP '^STEAM_[0-9]:%s$') OR (type = %i AND '%s' REGEXP REPLACE(REPLACE(ip, '.', '\\.') , '.0', '..{1,3}'))) AND unban_time IS NULL ORDER BY create_time LIMIT 1), '%s', %i, UNIX_TIMESTAMP())",
                                    STEAM_BAN_TYPE, sAuth[8], IP_BAN_TYPE, sIp, sEscapedName, g_iServerId);
    SB_Execute(sQuery, DBPrio_High);

    // SELECT type, steam, ip, name, reason, length, admin_id, admin_ip, create_time
    int iType    = results.FetchInt(0);
    results.FetchString(1, sAuth,    sizeof(sAuth));
    results.FetchString(2, sIp,      sizeof(sIp));
    results.FetchString(3, sName,    sizeof(sName));
    results.FetchString(4, sReason,  sizeof(sReason));
    int iLength  = results.FetchInt(5),
        iAdminId = results.FetchInt(6);
    results.FetchString(7, sAdminIp, sizeof(sAdminIp));
    int iTime    = results.FetchInt(8);

    PrintBan(client, sAuth, sIp, sName, sReason, iLength, iTime);

    InsertLocalBan(iType, sAuth, sIp, sName, sReason, iLength, iAdminId, sAdminIp, iTime);
    // Delay kick, otherwise ban information will not be printed to console
    CreateTimer(1.0, Timer_KickClient, GetClientUserId(client));
}

public void Query_AddedFromQueue(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    char sIdentity[20];
    pack.Reset();
    pack.ReadString(sIdentity, sizeof(sIdentity));
    delete pack;

    if (!error[0]) {
        DeleteLocalBan(sIdentity);
    }
}


/**
 * Natives
 */
public int Native_ReportPlayer(Handle plugin, int numParams)
{
    char sReason[256];
    int iClient = GetNativeCell(1),
        iTarget = GetNativeCell(2);
    GetNativeString(3, sReason, sizeof(sReason));

    char sEscapedName[MAX_NAME_LENGTH * 2 + 1], sEscapedReason[512], sEscapedTargetName[MAX_NAME_LENGTH * 2 + 1],
         sIp[16], sName[MAX_NAME_LENGTH + 1], sQuery[1024], sTargetAuth[20], sTargetIp[16], sTargetName[MAX_NAME_LENGTH + 1];
    GetClientAuthId(iTarget, AuthId_Steam2, sTargetAuth, sizeof(sTargetAuth));
    GetClientIP(iClient,                    sIp,         sizeof(sIp));
    GetClientIP(iTarget,                    sTargetIp,   sizeof(sTargetIp));
    GetClientName(iClient,                  sName,       sizeof(sName));
    GetClientName(iTarget,                  sTargetName, sizeof(sTargetName));

    DataPack hPack = new DataPack();
    hPack.WriteCell(ParseClientSerial(iClient));
    hPack.WriteCell(ParseClientSerial(iTarget));
    hPack.WriteString(sReason);

    SB_Escape(sName,       sEscapedName,       sizeof(sEscapedName));
    SB_Escape(sReason,     sEscapedReason,     sizeof(sEscapedReason));
    SB_Escape(sTargetName, sEscapedTargetName, sizeof(sEscapedTargetName));
    Format(sQuery, sizeof(sQuery), "INSERT INTO {{reports}} (name, steam, ip, reason, server_id, user_name, user_ip, create_time) \
                                    VALUES      ('%s', '%s', '%s', '%s', %i, '%s', '%s', UNIX_TIMESTAMP())",
                                    sEscapedTargetName, sTargetAuth, sTargetIp, sEscapedReason, g_iServerId, sEscapedName, sIp);
    SB_Query(Query_ReportPlayer, sQuery, hPack);

    return SP_ERROR_NONE;
}


/**
 * Stocks
 */
void DeleteLocalBan(const char[] sIdentity)
{
    if (!g_hSQLiteDB) {
        return;
    }

    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "DELETE FROM sb_bans \
                                    WHERE       (type = %i AND steam = '%s') \
                                       OR       (type = %i AND ip    = '%s')",
                                    STEAM_BAN_TYPE, sIdentity, IP_BAN_TYPE, sIdentity);
    SQL_FastQuery(g_hSQLiteDB, sQuery);
}

void DisplayBanTargetMenu(int client)
{
    char sTitle[128];
    Menu hMenu = new Menu(MenuHandler_Target);
    Format(sTitle, sizeof(sTitle), "%T:", "Ban player", client);
    hMenu.SetTitle(sTitle);
    hMenu.ExitBackButton = true;
    AddTargetsToMenu2(hMenu, client, COMMAND_FILTER_NO_BOTS|COMMAND_FILTER_CONNECTED);
    hMenu.Display(client, MENU_TIME_FOREVER);
}

void DisplayBanTimeMenu(int client)
{
    char sTitle[128];
    Menu hMenu = new Menu(MenuHandler_Time);
    Format(sTitle, sizeof(sTitle), "%T:", "Ban player", client);
    hMenu.SetTitle(sTitle);
    hMenu.ExitBackButton = true;

    int iFlags;
    char sFlags[32], sLength[16], sName[32];
    for (int i = 0, iSize = g_hBanTimes.Length; i < iSize; i++) {
        g_hBanTimes.GetString(i,       sName,   sizeof(sName));
        g_hBanTimesFlags.GetString(i,  sFlags,  sizeof(sFlags));
        g_hBanTimesLength.GetString(i, sLength, sizeof(sLength));
        iFlags = ReadFlagString(sFlags);

        if ((GetUserFlagBits(client) & iFlags) == iFlags) {
            hMenu.AddItem(sLength, sName);
        }
    }

    hMenu.Display(client, MENU_TIME_FOREVER);
}

int GetAdminId(int client)
{
    // If admins are enabled, return their admin id, otherwise return 0
    return SB_GetConfigValue("EnableAdmins") ? SB_GetAdminId(client) : 0;
}

bool GetLocalBan(bool bType, const char[] sAuth, const char[] sIp = "", char sBanAuth[20] = "", char sBanIp[16] = "", char sBanName[MAX_NAME_LENGTH + 1] = "", char sBanReason[128] = "", int &iBanLength = 0, int &iBanTime = 0)
{
    if (!g_hSQLiteDB) {
        return false;
    }

    char sQuery[1024];
    if (bType) {
        Format(sQuery, sizeof(sQuery), "SELECT steam, ip, name, reason, length, create_time \
                                        FROM   sb_bans \
                                        WHERE  ((type = %i AND steam = '%s') OR (type = %i AND ip = '%s')) \
                                          AND  (length = 0 OR create_time + length * 60 > %i OR (queued = 0 AND insert_time + 300 > %i))",
                                        STEAM_BAN_TYPE, sAuth[0] ? sAuth : "none", IP_BAN_TYPE, sIp[0] ? sIp : "none", GetTime(), GetTime());
    } else {
        Format(sQuery, sizeof(sQuery), "SELECT steam, ip, name, reason, length, create_time \
                                        FROM   sb_bans \
                                        WHERE  (steam = '%s' OR ip = '%s') \
                                          AND  (length = 0 OR create_time + length * 60 > %i OR (queued = 0 AND insert_time + 300 > %i))",
                                        sAuth[0] ? sAuth : "none", sIp[0] ? sIp : "none", GetTime(), GetTime());
    }

    DBResultSet hResults = SQL_Query(g_hSQLiteDB, sQuery);
    if (!hResults) {
        return false;
    }

    bool bResult = hResults.FetchRow();
    if (bResult) {
        hResults.FetchString(0, sBanAuth,   sizeof(sBanAuth));
        hResults.FetchString(1, sBanIp,     sizeof(sBanIp));
        hResults.FetchString(2, sBanName,   sizeof(sBanName));
        hResults.FetchString(3, sBanReason, sizeof(sBanReason));
        iBanLength = hResults.FetchInt(4);
        iBanTime   = hResults.FetchInt(5);
    }

    delete hResults;
    return bResult;
}

void InsertLocalBan(int iType, const char[] sAuth, const char[] sIp, const char[] sName, const char[] sReason, int iLength, int iAdminId, const char[] sAdminIp, int iTime, bool bQueued = false)
{
    char sEscapedName[MAX_NAME_LENGTH * 2 + 1], sEscapedReason[256], sQuery[1024];
    g_hSQLiteDB.Escape(sName,   sEscapedName,   sizeof(sEscapedName));
    g_hSQLiteDB.Escape(sReason, sEscapedReason, sizeof(sEscapedReason));

    Format(sQuery, sizeof(sQuery), "INSERT INTO sb_bans (type, steam, ip, name, reason, length, admin_id, admin_ip, queued, create_time, insert_time) \
                                    VALUES      (%i, '%s', '%s', '%s', '%s', %i, %i, '%s', %i, %i, %i)",
                                    iType, sAuth, sIp, sEscapedName, sEscapedReason, iLength, iAdminId, sAdminIp, bQueued ? 1 : 0, iTime, GetTime());
    SQL_FastQuery(g_hSQLiteDB, sQuery);

    #if defined _DEBUG
    PrintToServer("%sAdded local ban (%i,%s,%s,%s,%s,%i,%i,%s,%i,%i)", SB_PREFIX, iType, sAuth, sIp, sName, sReason, iLength, iAdminId, sAdminIp, iTime, bQueued ? 1 : 0);
    #endif
}

void PrintBan(int iClient, const char[] sAuth, const char[] sIp, const char[] sName, const char[] sReason, int iLength, int iTime)
{
    PrintToConsole(iClient, "===============================================");
    PrintToConsole(iClient, "%sYou are banned from this server.",  SB_PREFIX);

    if (iLength) {
        char sLength[64];
        SecondsToString(sLength, sizeof(sLength), iTime + (iLength * 60) - GetTime());
        PrintToConsole(iClient, "%sYou have %s left on your ban.", SB_PREFIX, sLength);
    }
    if (sName[0]) {
        PrintToConsole(iClient, "%sName:\t\t%s",                   SB_PREFIX, sName);
    }
    if (sAuth[0]) {
        PrintToConsole(iClient, "%sSteam ID:\t\t%s",               SB_PREFIX, sAuth);
    }
    if (sIp[0]) {
        PrintToConsole(iClient, "%sIP address:\t%s",               SB_PREFIX, sIp);
    }
    if (sReason[0]) {
        PrintToConsole(iClient, "%sReason:\t\t%s",                 SB_PREFIX, sReason);
    }

    PrintToConsole(iClient, "%sYou can protest your ban at %s.",   SB_PREFIX, g_sWebsite);
    PrintToConsole(iClient, "===============================================");
}

void SecondsToString(char[] sBuffer, int iLength, int iSecs, bool bTextual = true)
{
    if (bTextual) {
        char sDesc[6][8]    = {"mo",              "wk",             "d",          "hr",    "min", "sec"};
        int iCount, iDiv[6] = {60 * 60 * 24 * 30, 60 * 60 * 24 * 7, 60 * 60 * 24, 60 * 60, 60,    1};
        sBuffer[0]          = '\0';

        for (int i = 0; i < sizeof(iDiv); i++) {
            if ((iCount = iSecs / iDiv[i]) > 0) {
                Format(sBuffer, iLength, "%s%i %s, ", sBuffer, iCount, sDesc[i]);
                iSecs %= iDiv[i];
            }
        }
        sBuffer[strlen(sBuffer) - 2] = '\0';
    } else {
        int iHours = iSecs  / 60 / 60;
        iSecs     -= iHours * 60 * 60;
        int iMins  = iSecs  / 60;
        iSecs     %= 60;
        Format(sBuffer, iLength, "%i:%i:%i", iHours, iMins, iSecs);
    }
}
