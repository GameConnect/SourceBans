#include <sourcemod>
#include <sourcebans>

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
int g_ConnectLock = 0;
int g_Sequence    = 0;
int g_ServerId;
int g_ServerPort;
char g_ConfigPath[PLATFORM_MAX_PATH];
char g_ServerHost[128];
KeyValues g_Config;
Database g_Database;
Handle g_OnInitForward;


/**
 * Plugin Forwards
 */
public APLRes AskPluginLoad2(Handle myself, bool late, char[] error, int err_max)
{
    CreateNative("SB_Connect",         Native_Connect);
    CreateNative("SB_Escape",          Native_Escape);
    CreateNative("SB_Execute",         Native_Execute);
    CreateNative("SB_GetConfigFloat",  Native_GetConfigFloat);
    CreateNative("SB_GetConfigInt",    Native_GetConfigInt);
    CreateNative("SB_GetConfigString", Native_GetConfigString);
    CreateNative("SB_GetServerId",     Native_GetServerId);
    CreateNative("SB_Init",            Native_Init);
    CreateNative("SB_IsConnected",     Native_IsConnected);
    CreateNative("SB_Query",           Native_Query);
    RegPluginLibrary("sourcebans");

    return APLRes_Success;
}

public void OnPluginStart()
{
    LoadTranslations("common.phrases");

    CreateConVar("sb_version", SB_VERSION, "Advanced admin and ban management for the Source engine", FCVAR_NOTIFY);

    BuildPath(Path_SM, g_ConfigPath, sizeof(g_ConfigPath), "configs/sourcebans.cfg");

    g_OnInitForward = CreateGlobalForward("SB_OnInit", ET_Event);
}

public void OnMapStart()
{
    LoadConfig();
    RequestDatabaseConnection();
}

public void OnMapEnd()
{
    // Clean up on map end just so we can start a fresh connection when we need it later.
    delete g_Database;
}


/**
 * Connect Callback
 */
public void OnDatabaseConnect(Database db, const char[] error, any data)
{
    #if defined _DEBUG
    PrintToServer("%sOnDatabaseConnect(%x, %d) ConnectLock=%d", SB_PREFIX, db, data, g_ConnectLock);
    #endif

    // If this happens to be an old connection request, ignore it.
    if (data != g_ConnectLock || g_Database) {
        if (db) {
            delete db;
        }
        return;
    }

    g_ConnectLock = 0;
    g_Database    = db;

    // See if the connection is valid.  If not, don't un-mark the caches
    // as needing rebuilding, in case the next connection request works.
    if (!g_Database) {
        LogError("%T (%s)", "Could not connect to database", LANG_SERVER, error);
        return;
    }

    g_Database.SetCharset("utf8");

    // Retrieve server ID from the database
    char query[128];
    g_Database.Format(query, sizeof(query), "SELECT id \
                                             FROM   sb_servers \
                                             WHERE  host = '%s' \
                                               AND  port = %i",
                                             g_ServerHost, g_ServerPort);

    g_Database.Query(Query_ServerSelect, query);
}


/**
 * Query Callbacks
 */
public void Query_InvokeCallback(Database db, DBResultSet results, const char[] error, DataPack pack)
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

public void Query_LogError(Database db, DBResultSet results, const char[] error, any data)
{
    if (error[0]) {
        LogError("%T (%s)", "Failed to query database", LANG_SERVER, error);
    }
}

public void Query_ServerInsert(Database db, DBResultSet results, const char[] error, any data)
{
    if (error[0]) {
        LogError("%T (%s)", "Failed to query database", LANG_SERVER, error);
        return;
    }

    // Store server ID locally
    g_ServerId = results.InsertId;

    Call_StartForward(g_OnInitForward);
    Call_Finish();
}

public void Query_ServerSelect(Database db, DBResultSet results, const char[] error, any data)
{
    if (error[0]) {
        LogError("%T (%s)", "Failed to query database", LANG_SERVER, error);
        return;
    }
    if (!results.FetchRow()) {
        char folder[32], query[256];
        GetGameFolderName(folder, sizeof(folder));
        g_Database.Format(query, sizeof(query), "INSERT INTO sb_servers (game_id, host, port, enabled) \
                                                 VALUES ((SELECT id FROM sb_games WHERE folder = '%s'), '%s', %i, 1)",
                                                 folder, g_ServerHost, g_ServerPort);

        g_Database.Query(Query_ServerInsert, query);
        return;
    }

    // Store server ID locally
    g_ServerId = results.FetchInt(0);

    Call_StartForward(g_OnInitForward);
    Call_Finish();
}


/**
 * Natives
 */
public int Native_Connect(Handle plugin, int numParams)
{
    if (g_ConnectLock) {
        return;
    }

    RequestDatabaseConnection();
}

public int Native_Escape(Handle plugin, int numParams)
{
    // Get max length for the string buffer
    int maxlength = GetNativeCell(3);
    if (maxlength <= 0) {
        return false;
    }

    char[] buffer = new char[maxlength],
           string = new char[maxlength];
    GetNativeString(1, string, maxlength);

    any written = GetNativeCellRef(4);
    bool success = g_Database.Escape(string, buffer, maxlength, written);

    // Store value in string buffer
    SetNativeString(2, buffer, maxlength);
    return success;
}

public int Native_Execute(Handle plugin, int numParams)
{
    if (!g_Database) {
        return;
    }

    char query[4096];
    GetNativeString(1, query, sizeof(query));

    DBPriority prio = GetNativeCell(2);

    g_Database.Query(Query_LogError, query, _, prio);
}

public int Native_GetConfigFloat(Handle plugin, int numParams)
{
    char key[32];
    GetNativeString(1, key, sizeof(key));

    return view_as<int>(g_Config.GetFloat(key));
}

public int Native_GetConfigInt(Handle plugin, int numParams)
{
    char key[32];
    GetNativeString(1, key, sizeof(key));

    return g_Config.GetNum(key);
}

public int Native_GetConfigString(Handle plugin, int numParams)
{
    int maxlength = GetNativeCell(3);
    if (maxlength <= 0) {
        return;
    }

    char key[32];
    GetNativeString(1, key, sizeof(key));

    char[] buffer = new char[maxlength];
    g_Config.GetString(key, buffer, maxlength);

    SetNativeString(2, buffer, maxlength);
}

public int Native_GetServerId(Handle plugin, int numParams)
{
    return g_ServerId;
}

public int Native_Init(Handle plugin, int numParams)
{
    // If config is loaded and server ID has been retrieved, call init forward
    if (g_Config && g_ServerId) {
        Call_StartForward(g_OnInitForward);
        Call_Finish();
    }
}

public int Native_IsConnected(Handle plugin, int numParams)
{
    return !!g_Database;
}

public int Native_Query(Handle plugin, int numParams)
{
    if (!g_Database) {
        return;
    }

    char query[4096];
    GetNativeString(2, query, sizeof(query));

    Function callback = GetNativeFunction(1);
    any data = GetNativeCell(3);
    DBPriority prio = GetNativeCell(4);

    DataPack pack = new DataPack();
    pack.WriteCell(plugin);
    pack.WriteFunction(callback);
    pack.WriteCell(data);

    g_Database.Query(Query_InvokeCallback, query, pack, prio);
}


/**
 * Stocks
 */
void LoadConfig()
{
    delete g_Config;

    g_Config = new KeyValues("SourceBans");

    if (!g_Config.ImportFromFile(g_ConfigPath))
    {
        SetFailState("%sFile not found, corrupt or in the wrong format: %s", SB_PREFIX, g_ConfigPath);
        return;
    }

    g_Config.GetString("ServerHost", g_ServerHost, sizeof(g_ServerHost));
    g_ServerPort = g_Config.GetNum("ServerPort");
}

void RequestDatabaseConnection()
{
    g_ConnectLock = ++g_Sequence;

    if (SQL_CheckConfig("sourcebans")) {
        Database.Connect(OnDatabaseConnect, "sourcebans", g_ConnectLock);
    } else {
        Database.Connect(OnDatabaseConnect, "default", g_ConnectLock);
    }
}
