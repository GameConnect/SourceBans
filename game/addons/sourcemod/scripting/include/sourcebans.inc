/**
 * SourceBans Include File
 *
 * @author GameConnect
 * @version 2.0.0
 * @copyright SourceBans (C)2007-2016 GameConnect.net.  All rights reserved.
 * @package SourceBans
 * @link http://www.sourcebans.net
 */

#if defined _sourcebans_included
  #endinput
#endif
#define _sourcebans_included

#define SB_PREFIX	"[SourceBans] "
#define SB_VERSION	"2.0.0-dev"

/**
 * When connected to SourceBans database
 *
 * @param db	The database handle
 */
forward void SB_OnConnect(Database db);

/**
 * When SourceBans config is reloaded
 */
forward void SB_OnReload();


/**
 * Connect to SourceBans database
 */
native void SB_Connect();

/**
 * Escapes a database string for literal insertion
 *
 * @param string		String to quote.
 * @param buffer		Buffer to store quoted string in.
 * @param maxlength	Maximum length of the buffer.
 * @param written		Optionally returns the number of bytes written.
 * @return					True on success, false if buffer is not big enough.
 *									The buffer must be at least 2*strlen(string)+1.
 * @error						Invalid database or statement Handle.
 */
native bool SB_Escape(const char[] string, char[] buffer, int maxlength, int &written = 0);

/**
 * Execute query on SourceBans database and ignore result set
 *
 * @param query	Query string.
 * @param prio	Priority queue to use.
 */
native void SB_Execute(const char[] query, DBPriority prio = DBPrio_Normal);

/**
 * Get string value from SourceBans config
 *
 * @param key				The key of the value to retrieve
 * @param buffer		The string buffer to store the value in
 * @param maxlength	The max length of the string buffer
 */
native void SB_GetConfigString(const char[] key, char[] buffer, int maxlength);

/**
 * Get value from SourceBans config
 *
 * @param key	The key of the value to retrieve
 * @return		The value
 */
native any SB_GetConfigValue(const char[] key);

/**
 * Initialize third party SourceBans plugin in case it was late loaded
 */
native void SB_Init();

/**
 * Checks if connected to SourceBans database
 *
 * @return True if connected, false otherwise
 */
native bool SB_IsConnected();

/**
 * Query SourceBans database
 *
 * @param callback	Callback.
 * @param query			Query string.
 * @param data			Extra data value to pass to the callback.
 * @param prio			Priority queue to use.
 */
native void SB_Query(SQLQueryCallback callback, const char[] query, any data = 0, DBPriority prio = DBPrio_Normal);

/**
 * Reload SourceBans config
 */
native void SB_Reload();

public SharedPlugin __pl_sourcebans =
{
    name = "sourcebans",
    file = "sourcebans.smx",
    #if defined REQUIRE_PLUGIN
    required = 1
    #else
    required = 0
    #endif
};

#if !defined REQUIRE_PLUGIN
public __pl_sourcebans_SetNTVOptional()
{
    MarkNativeAsOptional("SB_Connect");
    MarkNativeAsOptional("SB_Escape");
    MarkNativeAsOptional("SB_Execute");
    MarkNativeAsOptional("SB_GetConfigString");
    MarkNativeAsOptional("SB_GetConfigValue");
    MarkNativeAsOptional("SB_Init");
    MarkNativeAsOptional("SB_Query");
    MarkNativeAsOptional("SB_Reload");
}
#endif

stock bool DisablePlugin(const char[] file)
{
    char sNewPath[PLATFORM_MAX_PATH + 1], sOldPath[PLATFORM_MAX_PATH + 1];
    BuildPath(Path_SM, sNewPath, sizeof(sNewPath), "plugins/disabled/%s.smx", file);
    BuildPath(Path_SM, sOldPath, sizeof(sOldPath), "plugins/%s.smx", file);

    // If plugins/<file>.smx does not exist, ignore
    if (!FileExists(sOldPath)) {
        return false;
    }

    // If plugins/disabled/<file>.smx exists, delete it
    if (FileExists(sNewPath)) {
        DeleteFile(sNewPath);
    }

    // Unload plugins/<file>.smx and move it to plugins/disabled/<file>.smx
    ServerCommand("sm plugins unload %s", file);
    RenameFile(sNewPath, sOldPath);
    LogMessage("plugins/%s.smx was unloaded and moved to plugins/disabled/%s.smx", file, file);
    return true;
}

stock int ParseClientSerial(int client)
{
    return client ? GetClientSerial(client) : 0;
}

stock bool ParseClientFromSerial(int &client, bool defaultValue = false)
{
    if (!client) {
        return defaultValue;
    }

    client = GetClientFromSerial(client);
    return client > 0;
}
