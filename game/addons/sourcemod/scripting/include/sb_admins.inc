#if defined _sb_admins_included
  #endinput
#endif
#define _sb_admins_included


/**
 * Returns the SourceBans admin ID of a client.
 *
 * @param   client  The index of the client
 * @return              The admin ID of the client
 */
native int SB_GetAdminId(int client);


public SharedPlugin __pl_sb_admins =
{
    name = "sb_admins",
    file = "sb_admins.smx",
    #if defined REQUIRE_PLUGIN
    required = 1
    #else
    required = 0
    #endif
};

#if !defined REQUIRE_PLUGIN
public __pl_sb_admins_SetNTVOptional()
{
    MarkNativeAsOptional("SB_GetAdminId");
}
#endif
