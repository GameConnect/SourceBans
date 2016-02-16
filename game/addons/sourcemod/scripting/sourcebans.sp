/**
 * SourceBans Core Plugin
 *
 * @author GameConnect
 * @version 2.0.0
 * @copyright SourceBans (C)2007-2016 GameConnect.net.  All rights reserved.
 * @package SourceBans
 * @link http://www.sourcebans.net
 */

#include <sourcemod>
#include <sourcebans>
#include <regex>

#pragma newdecls required
#pragma semicolon 1

//#define _DEBUG

public Plugin myinfo =
{
    name        = "SourceBans",
    author      = "GameConnect",
    description = "Advanced admin and ban management for the Source engine",
    version     = SB_VERSION,
    url         = "http://www.sourcebans.net"
};


/**
 * Globals
 */
enum ConfigState
{
    ConfigState_None = 0,
    ConfigState_Config,
    ConfigState_Reasons,
    ConfigState_Hacking,
    ConfigState_Times,
    ConfigState_Loaded
}

ConfigState g_iConfigState;
int g_iConnectLock = 0;
int g_iSequence    = 0;
int g_iServerPort;
StringMap g_hConfig;
SMCParser g_hConfigParser;
Database g_hDatabase;
ArrayList g_hBanReasons;
ArrayList g_hBanTimes;
ArrayList g_hBanTimesFlags;
ArrayList g_hBanTimesLength;
ArrayList g_hHackingReasons;
Handle g_hOnConnect;
Handle g_hOnReload;
char g_sConfigFile[PLATFORM_MAX_PATH];
char g_sDatabasePrefix[16];
char g_sServerIp[16];


/**
 * Plugin Forwards
 */
public APLRes AskPluginLoad2(Handle myself, bool late, char[] error, int err_max)
{
    CreateNative("SB_Connect",         Native_Connect);
    CreateNative("SB_Escape",          Native_Escape);
    CreateNative("SB_Execute",         Native_Execute);
    CreateNative("SB_GetConfigString", Native_GetConfigString);
    CreateNative("SB_GetConfigValue",  Native_GetConfigValue);
    CreateNative("SB_Init",            Native_Init);
    CreateNative("SB_IsConnected",     Native_IsConnected);
    CreateNative("SB_Query",           Native_Query);
    CreateNative("SB_Reload",          Native_Reload);
    RegPluginLibrary("sourcebans");

    return APLRes_Success;
}

public void OnPluginStart()
{
    CreateConVar("sb_version", SB_VERSION, "Advanced admin and ban management for the Source engine", FCVAR_NOTIFY|FCVAR_PLUGIN);
    RegAdminCmd("sb_reload", Command_Reload, ADMFLAG_RCON, "Reload SourceBans config and ban reason menu options");

    LoadTranslations("common.phrases");
    LoadTranslations("sourcebans.phrases");
    BuildPath(Path_SM, g_sConfigFile, sizeof(g_sConfigFile), "configs/sourcebans.cfg");

    g_hOnConnect      = CreateGlobalForward("SB_OnConnect", ET_Event, Param_Cell);
    g_hOnReload       = CreateGlobalForward("SB_OnReload",  ET_Event);
    g_hConfig         = new StringMap();
    g_hBanReasons     = new ArrayList(256);
    g_hBanTimes       = new ArrayList(256);
    g_hBanTimesFlags  = new ArrayList(256);
    g_hBanTimesLength = new ArrayList(256);
    g_hHackingReasons = new ArrayList(256);

    g_hConfigParser   = new SMCParser();
    g_hConfigParser.OnEnterSection = ReadConfig_NewSection;
    g_hConfigParser.OnKeyValue     = ReadConfig_KeyValue;
    g_hConfigParser.OnLeaveSection = ReadConfig_EndSection;

    int iServerIp     = GetConVarInt(FindConVar("hostip"));
    g_iServerPort     = GetConVarInt(FindConVar("hostport"));
    Format(g_sServerIp, sizeof(g_sServerIp), "%i.%i.%i.%i", (iServerIp >> 24) & 0xFF,
                                                            (iServerIp >> 16) & 0xFF,
                                                            (iServerIp >>  8) & 0xFF,
                                                            iServerIp         & 0xFF);

    // Store server IP and port locally
    g_hConfig.SetString("ServerIP",    g_sServerIp);
    g_hConfig.SetValue("ServerPort",   g_iServerPort);
    // Store whether the admins plugin is enabled or disabled
    g_hConfig.SetValue("EnableAdmins", LibraryExists("sb_admins"));
}

public void OnMapStart()
{
    SB_Reload();
    SB_Connect();
}

public void OnMapEnd()
{
    delete g_hDatabase;
}

public void OnLibraryAdded(const char[] name)
{
    if (StrEqual(name, "sb_admins")) {
        g_hConfig.SetValue("EnableAdmins", true);
    }
}

public void OnLibraryRemoved(const char[] name)
{
    if (StrEqual(name, "sb_admins")) {
        g_hConfig.SetValue("EnableAdmins", false);
    }
}


/**
 * Commands
 */
public Action Command_Reload(int client, int args)
{
    SB_Reload();
    return Plugin_Handled;
}


/**
 * Config Parser
 */
public SMCResult ReadConfig_EndSection(SMCParser smc)
{
    return SMCParse_Continue;
}

public SMCResult ReadConfig_KeyValue(SMCParser smc, const char[] key, const char[] value, bool key_quotes, bool value_quotes)
{
    if (!key[0]) {
        return SMCParse_Continue;
    }

    switch (g_iConfigState) {
        case ConfigState_Config:
        {
            // If value is an integer
            if (StrEqual("Addban",           key, false) ||
                StrEqual("ProcessQueueTime", key, false) ||
                StrEqual("RequireSiteLogin", key, false) ||
                StrEqual("Unban",            key, false)) {
                g_hConfig.SetValue(key,  StringToInt(value));
            }
            // If value is a float
            else if (StrEqual("RetryTime",   key, false)) {
                g_hConfig.SetValue(key,  StringToFloat(value));
            }
            // If value is a string
            else if (value[0]) {
                g_hConfig.SetString(key, value);
            }
        }
        case ConfigState_Hacking:
            g_hHackingReasons.PushString(value);
        case ConfigState_Reasons:
            g_hBanReasons.PushString(value);
        case ConfigState_Times:
        {
            if (StrEqual("flags",       key, false)) {
                g_hBanTimesFlags.PushString(value);
            }
            else if (StrEqual("length", key, false)) {
                g_hBanTimesLength.PushString(value);
            }
        }
    }
    return SMCParse_Continue;
}

public SMCResult ReadConfig_NewSection(SMCParser smc, const char[] name, bool opt_quotes)
{
    if (StrEqual("Config",              name, false)) {
        g_iConfigState = ConfigState_Config;
    }
    else if (StrEqual("BanReasons",     name, false)) {
        g_iConfigState = ConfigState_Reasons;
    }
    else if (StrEqual("BanTimes",       name, false)) {
        g_iConfigState = ConfigState_Times;
    }
    else if (StrEqual("HackingReasons", name, false)) {
        g_iConfigState = ConfigState_Hacking;
    }
    else if (g_iConfigState == ConfigState_Times) {
        g_hBanTimes.PushString(name);
    }
    return SMCParse_Continue;
}


/**
 * Query Callbacks
 */
public void Query_ServerSelect(Database db, DBResultSet results, const char[] error, any data)
{
    if (error[0]) {
        LogError("%T (%s)", "Failed to query database", LANG_SERVER, error);
        return;
    }
    if (results.FetchRow()) {
        // Store server ID locally
        g_hConfig.SetValue("ServerID", results.FetchInt(0));

        Call_StartForward(g_hOnConnect);
        Call_PushCell(g_hDatabase);
        Call_Finish();
        return;
    }

    char sFolder[32], sQuery[1024];
    GetGameFolderName(sFolder, sizeof(sFolder));

    Format(sQuery, sizeof(sQuery), "INSERT INTO {{servers}} (host, port, game_id) \
                                    VALUES      ('%s', %i, (SELECT id FROM {{games}} WHERE folder = '%s'))",
                                    g_sServerIp, g_iServerPort, sFolder);
    SB_Query(Query_ServerInsert, sQuery);
}

public void Query_ServerInsert(Database db, DBResultSet results, const char[] error, any data)
{
    if (error[0]) {
        LogError("%T (%s)", "Failed to query database", LANG_SERVER, error);
        return;
    }

    // Store server ID locally
    g_hConfig.SetValue("ServerID", results.InsertId);

    Call_StartForward(g_hOnConnect);
    Call_PushCell(g_hDatabase);
    Call_Finish();
}

public void Query_ExecuteCallback(Database db, DBResultSet results, const char[] error, DataPack pack)
{
    pack.Reset();
    Handle plugin = pack.ReadCell();
    Function callback = pack.ReadFunction();
    any data = pack.ReadCell();
    delete pack;

    Call_StartFunction(plugin, callback);
    Call_PushCell(db);
    Call_PushCell(results);
    Call_PushString(error);
    Call_PushCell(data);
    Call_Finish();
}

public void Query_ErrorCheck(Database db, DBResultSet results, const char[] error, any data)
{
    if (error[0]) {
        LogError("%T (%s)", "Failed to query database", LANG_SERVER, error);
    }
}


/**
 * Connect Callback
 */
public void OnDatabaseConnect(Database db, const char[] error, any data)
{
    #if defined _DEBUG
    PrintToServer("%sOnDatabaseConnect(%x, %d) ConnectLock=%d", SB_PREFIX, db, data, g_iConnectLock);
    #endif

    // If this happens to be an old connection request, ignore it.
    if (data != g_iConnectLock || g_hDatabase) {
        if (db) {
            delete db;
        }
        return;
    }

    g_iConnectLock = 0;
    g_hDatabase    = db;

    // See if the connection is valid.  If not, don't un-mark the caches
    // as needing rebuilding, in case the next connection request works.
    if (!g_hDatabase) {
        LogError("%T (%s)", "Could not connect to database", LANG_SERVER, error);
        return;
    }

    g_hDatabase.SetCharset("utf8");

    // Select server from the database
    char sQuery[1024];
    Format(sQuery, sizeof(sQuery), "SELECT id \
                                    FROM   {{servers}} \
                                    WHERE  host = '%s' \
                                      AND  port = %i",
                                    g_sServerIp, g_iServerPort);
    SB_Query(Query_ServerSelect, sQuery);
}


/**
 * Natives
 */
public int Native_Connect(Handle plugin, int numParams)
{
    if (g_iConnectLock) {
        return;
    }

    g_iConnectLock = ++g_iSequence;
    // Connect using the "sourcebans" section, or the "default" section if "sourcebans" does not exist
    Database.Connect(OnDatabaseConnect, SQL_CheckConfig("sourcebans") ? "sourcebans" : "default", g_iConnectLock);
}

public int Native_Escape(Handle plugin, int numParams)
{
    // Get max length for the string buffer
    int iLen = GetNativeCell(3);
    if (iLen <= 0) {
        return false;
    }

    char[] sData = new char[iLen], sBuffer = new char[iLen];
    GetNativeString(1, sData, iLen);

    any written = GetNativeCellRef(4);
    bool success = g_hDatabase.Escape(sData, sBuffer, iLen, written);

    // Store value in string buffer
    SetNativeString(2, sBuffer, iLen);
    return success;
}

public int Native_Execute(Handle plugin, int numParams)
{
    if (!SB_IsConnected()) {
        return;
    }

    char sQuery[4096];
    GetNativeString(1, sQuery, sizeof(sQuery));

    DBPriority prio = GetNativeCell(2);

    ExecuteQuery(Query_ErrorCheck, sQuery, 0, prio);
}

public int Native_GetConfigString(Handle plugin, int numParams)
{
    // Get max length for the string buffer
    int iLen = GetNativeCell(3);
    if (iLen <= 0) {
        return;
    }

    // Get value for key
    char sKey[32];
    char[] sValue = new char[iLen];
    GetNativeString(1, sKey, sizeof(sKey));
    g_hConfig.GetString(sKey, sValue, iLen);

    // Store value in string buffer
    SetNativeString(2, sValue, iLen);
}

public int Native_GetConfigValue(Handle plugin, int numParams)
{
    // Get value for key
    char sKey[32];
    int iValue;
    GetNativeString(1, sKey, sizeof(sKey));
    g_hConfig.GetValue(sKey, iValue);

    // Return value
    return iValue;
}

public int Native_Init(Handle plugin, int numParams)
{
    // If config is loaded, call reload forward
    if (g_iConfigState == ConfigState_Loaded) {
        Call_StartForward(g_hOnReload);
        Call_Finish();
    }

    // If server ID has been fetched, call connect forward
    int iServerId;
    if (g_hConfig.GetValue("ServerID", iServerId)) {
        Call_StartForward(g_hOnConnect);
        Call_PushCell(g_hDatabase);
        Call_Finish();
    }
}

public int Native_IsConnected(Handle plugin, int numParams)
{
    return !!g_hDatabase;
}

public int Native_Query(Handle plugin, int numParams)
{
    if (!SB_IsConnected()) {
        return;
    }

    char sQuery[4096];
    GetNativeString(2, sQuery, sizeof(sQuery));

    Function callback = GetNativeFunction(1);
    any data = GetNativeCell(3);
    DBPriority prio = GetNativeCell(4);

    DataPack hPack = new DataPack();
    hPack.WriteCell(plugin);
    hPack.WriteFunction(callback);
    hPack.WriteCell(data);

    ExecuteQuery(Query_ExecuteCallback, sQuery, hPack, prio);
}

public int Native_Reload(Handle plugin, int numParams)
{
    if (!FileExists(g_sConfigFile)) {
        SetFailState("%sFile not found: %s", SB_PREFIX, g_sConfigFile);
    }

    // Empty ban reason and ban time arrays
    g_hBanReasons.Clear();
    g_hBanTimes.Clear();
    g_hBanTimesFlags.Clear();
    g_hBanTimesLength.Clear();
    g_hHackingReasons.Clear();

    // Reset config state
    g_iConfigState  = ConfigState_None;

    // Parse config file
    SMCError iError = g_hConfigParser.ParseFile(g_sConfigFile);
    if (iError != SMCError_Okay) {
        char sError[64] = "Fatal parse error";
        g_hConfigParser.GetErrorString(iError, sError, sizeof(sError));
        LogError(sError);
        return;
    }

    g_iConfigState  = ConfigState_Loaded;

    g_hConfig.GetString("DatabasePrefix", g_sDatabasePrefix, sizeof(g_sDatabasePrefix));
    g_hConfig.GetString("ServerIP",       g_sServerIp,       sizeof(g_sServerIp));
    g_hConfig.GetValue("ServerPort",      g_iServerPort);
    g_hConfig.SetValue("BanReasons",      g_hBanReasons);
    g_hConfig.SetValue("BanTimes",        g_hBanTimes);
    g_hConfig.SetValue("BanTimesFlags",   g_hBanTimesFlags);
    g_hConfig.SetValue("BanTimesLength",  g_hBanTimesLength);
    g_hConfig.SetValue("HackingReasons",  g_hHackingReasons);

    Call_StartForward(g_hOnReload);
    Call_Finish();
}


/**
 * Stocks
 */
void ExecuteQuery(SQLQueryCallback callback, char sQuery[4096], any data = 0, DBPriority prio = DBPrio_Normal)
{
    // Format {{table}} as DatabasePrefixtable
    char sSearch[65], sReplace[65], sTable[65];
    static Regex hTables;
    if (!hTables) {
        hTables = new Regex("\\{\\{([0-9a-zA-Z\\$_]+?)\\}\\}");
    }

    while (hTables.Match(sQuery) > 0) {
        hTables.GetSubString(0, sSearch, sizeof(sSearch));
        hTables.GetSubString(1, sTable,  sizeof(sTable));
        Format(sReplace, sizeof(sReplace), "%s%s", g_sDatabasePrefix, sTable);

        ReplaceString(sQuery, sizeof(sQuery), sSearch, sReplace);
    }

    g_hDatabase.Query(callback, sQuery, data, prio);
}
