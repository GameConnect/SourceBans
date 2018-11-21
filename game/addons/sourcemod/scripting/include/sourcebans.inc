#if defined _sourcebans_included
  #endinput
#endif
#define _sourcebans_included

#define SB_PREFIX   "[SourceBans] "
#define SB_VERSION  "2.0.0-dev"


/**
 * Called when the SourceBans config is loaded and the server ID has been retrieved.
 */
forward void SB_OnInit();


/**
 * Requests a database connection.
 */
native void SB_Connect();

/**
 * Escapes a database string for literal insertion.
 *
 * @param string        String to quote.
 * @param buffer        Buffer to store quoted string in.
 * @param maxlength Maximum length of the buffer.
 * @param written       Optionally returns the number of bytes written.
 * @return                  True on success, false if buffer is not big enough.
 *                                  The buffer must be at least 2*strlen(string)+1.
 * @error                       Invalid database or statement Handle.
 */
native bool SB_Escape(const char[] string, char[] buffer, int maxlength, int &written = 0);

/**
 * Executes a query on the SourceBans database and ignore the result set.
 *
 * @param query Query string.
 * @param prio  Priority queue to use.
 */
native void SB_Execute(const char[] query, DBPriority prio = DBPrio_Normal);

/**
 * Retrieves a float value from the SourceBans config.
 *
 * @param key   The key of the value to retrieve
 * @return      The value
 */
native float SB_GetConfigFloat(const char[] key);

/**
 * Retrieves an integer value from the SourceBans config.
 *
 * @param key   The key of the value to retrieve
 * @return      The value
 */
native int SB_GetConfigInt(const char[] key);

/**
 * Retrieves a string value from the SourceBans config.
 *
 * @param key               The key of the value to retrieve
 * @param buffer        The string buffer to store the value in
 * @param maxlength The max length of the string buffer
 */
native void SB_GetConfigString(const char[] key, char[] buffer, int maxlength);

/**
 * Returns the server ID.
 *
 * @return      The server ID
 */
native int SB_GetServerId();

/**
 * Initializes a third party SourceBans plugin in case it was late loaded.
 */
native void SB_Init();

/**
 * Returns whether we're connected to the SourceBans database.
 *
 * @return True if connected, false otherwise
 */
native bool SB_IsConnected();

/**
 * Executes a query on the SourceBans database.
 *
 * @param callback  Callback.
 * @param query         Query string.
 * @param data          Extra data value to pass to the callback.
 * @param prio          Priority queue to use.
 */
native void SB_Query(SQLQueryCallback callback, const char[] query, any data = 0, DBPriority prio = DBPrio_Normal);


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
    MarkNativeAsOptional("SB_GetConfigFloat");
    MarkNativeAsOptional("SB_GetConfigInt");
    MarkNativeAsOptional("SB_GetConfigString");
    MarkNativeAsOptional("SB_GetServerId");
    MarkNativeAsOptional("SB_Init");
    MarkNativeAsOptional("SB_IsConnected");
    MarkNativeAsOptional("SB_Query");
}
#endif
