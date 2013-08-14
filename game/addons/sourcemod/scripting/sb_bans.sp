/**
 * SourceBans Bans Plugin
 *
 * @author GameConnect
 * @version 2.0.0
 * @copyright SourceBans (C)2007-2013 GameConnect.net.  All rights reserved.
 * @package SourceBans
 * @link http://www.sourcebans.net
 */

#pragma semicolon 1

#include <sourcemod>
#include <sourcebans>
#include <sb_bans>

#undef REQUIRE_PLUGIN
#include <sb_admins>
#include <adminmenu>
#include <dbi>
#include <geoip>

#define STEAM_BAN_TYPE		0
#define IP_BAN_TYPE				1
#define DEFAULT_BAN_TYPE	STEAM_BAN_TYPE

//#define _DEBUG

public Plugin:myinfo =
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
new g_iBanTarget[MAXPLAYERS + 1];
new g_iBanTime[MAXPLAYERS + 1];
new g_iProcessQueueTime;
new g_iServerId;
new bool:g_bEnableAddBan;
new bool:g_bEnableUnban;
new bool:g_bOwnReason[MAXPLAYERS + 1];
new bool:g_bPlayerStatus[MAXPLAYERS + 1];
new Float:g_flRetryTime;
new Handle:g_hBanTimes;
new Handle:g_hBanTimesFlags;
new Handle:g_hBanTimesLength;
new Handle:g_hHackingMenu;
new Handle:g_hPlayerRecheck[MAXPLAYERS + 1];
new Handle:g_hProcessQueue;
new Handle:g_hReasonMenu;
new Handle:g_hSQLiteDB;
new Handle:g_hTopMenu;
new String:g_sServerIp[16];
new String:g_sWebsite[256];


/**
 * Plugin Forwards
 */
public APLRes:AskPluginLoad2(Handle:myself, bool:late, String:error[], err_max)
{
	CreateNative("SB_SubmitBan", Native_SubmitBan);
	RegPluginLibrary("sb_bans");
	
	return APLRes_Success;
}

public OnPluginStart()
{
	RegAdminCmd("sm_ban",    Command_Ban,    ADMFLAG_BAN,   "sm_ban <#userid|name> <minutes|0> [reason]");
	RegAdminCmd("sm_banip",  Command_BanIp,  ADMFLAG_BAN,   "sm_banip <ip|#userid|name> <time> [reason]");
	RegAdminCmd("sm_addban", Command_AddBan, ADMFLAG_RCON,  "sm_addban <time> <steamid> [reason]");
	RegAdminCmd("sm_unban",  Command_Unban,  ADMFLAG_UNBAN, "sm_unban <steamid|ip>");
	
	AddCommandListener(Command_Say, "say");
	AddCommandListener(Command_Say, "say2");
	AddCommandListener(Command_Say, "say_team");
	
	LoadTranslations("common.phrases");
	LoadTranslations("sourcebans.phrases");
	LoadTranslations("basebans.phrases");
	
	// Hook player_connect event to prevent connection spamming from people that are banned
	HookEvent("player_connect", Event_PlayerConnect, EventHookMode_Pre);
	
	g_hHackingMenu = CreateMenu(MenuHandler_Reason);
	g_hReasonMenu  = CreateMenu(MenuHandler_Reason);
	SetMenuExitBackButton(g_hHackingMenu, true);
	SetMenuExitBackButton(g_hReasonMenu,  true);
	
	// Account for late loading
	new Handle:hTopMenu;
	if(LibraryExists("adminmenu") && (hTopMenu = GetAdminTopMenu()))
		OnAdminMenuReady(hTopMenu);
	
	if(LibraryExists("sourcebans"))
		SB_Init();
	
	// Connect to local database
	decl String:sError[256] = "";
	g_hSQLiteDB    = SQLite_UseDatabase("sourcemod-local", sError, sizeof(sError));
	if(sError[0])
	{
		LogError("%T (%s)", "Could not connect to database", LANG_SERVER, sError);
		return;
	}
	
	// Create local bans table
	SQL_FastQuery(g_hSQLiteDB, "CREATE TABLE IF NOT EXISTS sb_bans (type INTEGER, steam TEXT PRIMARY KEY ON CONFLICT REPLACE, ip TEXT, name TEXT, reason TEXT, length INTEGER, admin_id INTEGER, admin_ip TEXT, queued BOOLEAN, create_time INTEGER, insert_time INTEGER)");
	
	// Process temporary bans every minute
	CreateTimer(60.0, Timer_ProcessTemp, _, TIMER_REPEAT);
}

public OnAdminMenuReady(Handle:topmenu)
{
	// Block us from being called twice
	if(topmenu == g_hTopMenu)
		return;
	
	// Save the handle
	g_hTopMenu = topmenu;
	
	// Find the "Player Commands" category
	new TopMenuObject:iPlayerCommands = FindTopMenuCategory(g_hTopMenu, ADMINMENU_PLAYERCOMMANDS);
	if(iPlayerCommands)
		AddToTopMenu(g_hTopMenu,
			"sm_ban",
			TopMenuObject_Item,
			MenuHandler_Ban,
			iPlayerCommands,
			"sm_ban",
			ADMFLAG_BAN);
}

public OnConfigsExecuted()
{
	if(DisablePlugin("basebans"))
	{
		// Re-add "Ban player" option to admin menu
		new Handle:hTopMenu;
		if(LibraryExists("adminmenu") && (hTopMenu = GetAdminTopMenu()))
		{
			g_hTopMenu = INVALID_HANDLE;
			OnAdminMenuReady(hTopMenu);
		}
	}
}


/**
 * Client Forwards
 */
public OnClientDisconnect(client)
{
	if(g_hPlayerRecheck[client])
		KillTimer(g_hPlayerRecheck[client]);
	
	g_hPlayerRecheck[client] = INVALID_HANDLE;
	g_bOwnReason[client]     = false;
	g_bPlayerStatus[client]  = false;
}

public OnClientPostAdminCheck(client)
{
	decl String:sAuth[20], String:sIp[16], String:sName[MAX_NAME_LENGTH + 1], String:sReason[128];
	new iLength, iTime;
	GetClientAuthString(client, sAuth, sizeof(sAuth));
	GetClientIP(client,         sIp,   sizeof(sIp));
	
	if(GetLocalBan(true, sAuth, sIp, sAuth, sIp, sName, sReason, iLength, iTime))
	{
		PrintBan(client, sAuth, sIp, sName, sReason, iLength, iTime);
		// Delay kick, otherwise ban information will not be printed to console
		CreateTimer(1.0, Timer_KickClient, GetClientUserId(client));
		return;
	}
	if(!SB_Connect() || StrContains("BOT STEAM_ID_LAN", sAuth) != -1)
	{
		g_bPlayerStatus[client] = true;
		return;
	}
	
	decl String:sQuery[1024];
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
public Action:OnBanClient(client, time, flags, const String:reason[], const String:kick_message[], const String:command[], any:admin)
{
	decl iType, String:sAdminIp[16], String:sAuth[20], String:sIp[16], String:sName[MAX_NAME_LENGTH + 1];
	new iAdminId    = GetAdminId(admin),
	    bool:bSteam = GetClientAuthString(client, sAuth, sizeof(sAuth));
	GetClientIP(client,   sIp,   sizeof(sIp));
	GetClientName(client, sName, sizeof(sName));
	
	// Set type depending on passed flags
	if(flags      & BANFLAG_AUTHID || ((flags & BANFLAG_AUTO) && bSteam))
		iType = STEAM_BAN_TYPE;
	else if(flags & BANFLAG_IP)
		iType = IP_BAN_TYPE;
	// If no valid flag was passed, block banning
	else
		return Plugin_Handled;
	
	if(admin)
		GetClientIP(admin, sAdminIp, sizeof(sAdminIp));
	else
		sAdminIp = g_sServerIp;
	if(!SB_Connect())
	{
		InsertLocalBan(iType, sAuth, sIp, sName, reason, time, iAdminId, sAdminIp, GetTime(), true);
		return Plugin_Handled;
	}
	if(time)
	{
		if(reason[0])
			ShowActivity2(admin, SB_PREFIX, "%t", "Banned player reason",      sName, time, reason);
		else
			ShowActivity2(admin, SB_PREFIX, "%t", "Banned player",             sName, time);
	}
	else
	{
		if(reason[0])
			ShowActivity2(admin, SB_PREFIX, "%t", "Permabanned player reason", sName, reason);
		else
			ShowActivity2(admin, SB_PREFIX, "%t", "Permabanned player",        sName);
	}
	
	new Handle:hPack = CreateDataPack();
	WritePackCell(hPack,   ParseClientSerial(admin));
	WritePackCell(hPack,   time);
	WritePackString(hPack, sAuth);
	WritePackString(hPack, sIp);
	WritePackString(hPack, sName);
	WritePackString(hPack, reason);
	WritePackCell(hPack,   iAdminId);
	WritePackString(hPack, sAdminIp);
	
	decl String:sEscapedName[MAX_NAME_LENGTH * 2 + 1], String:sEscapedReason[256], String:sQuery[1024];
	SB_Escape(sName,  sEscapedName,   sizeof(sEscapedName));
	SB_Escape(reason, sEscapedReason, sizeof(sEscapedReason));
	Format(sQuery, sizeof(sQuery), "INSERT INTO {{bans}} (type, steam, ip, name, reason, length, server_id, admin_id, admin_ip, create_time) \
	                                VALUES      (%i, '%s', '%s', '%s', '%s', %i, %i, NULLIF(%i, 0), '%s', UNIX_TIMESTAMP())",
	                                iType, sAuth, sIp, sEscapedName, sEscapedReason, time, g_iServerId, iAdminId, sAdminIp);
	SB_Query(Query_BanInsert, sQuery, hPack, DBPrio_High);
	
	LogAction(admin, client, "\"%L\" banned \"%L\" (minutes \"%i\") (reason \"%s\")", admin, client, time, reason);
	return Plugin_Handled;
}

public Action:OnBanIdentity(const String:identity[], time, flags, const String:reason[], const String:command[], any:admin)
{
	decl String:sAdminIp[16], String:sQuery[1024];
	new iAdminId    = GetAdminId(admin),
	    bool:bSteam = strncmp(identity, "STEAM_", 6) == 0;
	
	if(admin)
		GetClientIP(admin, sAdminIp, sizeof(sAdminIp));
	else
		sAdminIp = g_sServerIp;
	if(!SB_Connect())
	{
		if(bSteam)
			InsertLocalBan(STEAM_BAN_TYPE, identity, "", "", reason, time, iAdminId, sAdminIp, GetTime(), true);
		else
			InsertLocalBan(IP_BAN_TYPE,    "", identity, "", reason, time, iAdminId, sAdminIp, GetTime(), true);
		return Plugin_Handled;
	}
	
	new Handle:hPack = CreateDataPack();
	WritePackCell(hPack,   ParseClientSerial(admin));
	WritePackCell(hPack,   time);
	WritePackString(hPack, identity);
	WritePackString(hPack, reason);
	WritePackCell(hPack,   iAdminId);
	WritePackString(hPack, sAdminIp);
	
	if(flags      & BANFLAG_AUTHID || ((flags & BANFLAG_AUTO) && bSteam))
	{
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
	else if(flags & BANFLAG_IP     || ((flags & BANFLAG_AUTO) && !bSteam))
	{
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

public Action:OnRemoveBan(const String:identity[], flags, const String:command[], any:admin)
{
	decl String:sQuery[1024];
	new Handle:hPack = CreateDataPack();
	WritePackCell(hPack,   ParseClientSerial(admin));
	WritePackString(hPack, identity);
	
	if(flags      & BANFLAG_AUTHID)
		Format(sQuery, sizeof(sQuery), "SELECT 1 \
		                                FROM   {{bans}} \
		                                WHERE  type  = %i \
		                                  AND  steam REGEXP '^STEAM_[0-9]:%s$' \
		                                  AND  (length = 0 OR create_time + length * 60 > UNIX_TIMESTAMP()) \
		                                  AND  unban_time IS NULL",
		                                STEAM_BAN_TYPE, identity[8]);
	else if(flags & BANFLAG_IP)
		Format(sQuery, sizeof(sQuery), "SELECT 1 \
		                                FROM   {{bans}} \
		                                WHERE  type = %i \
		                                  AND  ip   = '%s' \
		                                  AND  (length = 0 OR create_time + length * 60 > UNIX_TIMESTAMP()) \
		                                  AND  unban_time IS NULL",
		                                IP_BAN_TYPE, identity);
	SB_Query(Query_UnbanSelect, sQuery, hPack);
	
	LogAction(admin, -1, "\"%L\" removed ban (filter \"%s\")", admin, identity);
	return Plugin_Handled;
}


/**
 * SourceBans Forwards
 */
public SB_OnConnect(Handle:database)
{
	g_iServerId = SB_GetConfigValue("ServerID");
}

public SB_OnReload()
{
	// Get values from SourceBans config and store them locally
	SB_GetConfigString("ServerIP", g_sServerIp, sizeof(g_sServerIp));
	SB_GetConfigString("Website",  g_sWebsite,  sizeof(g_sWebsite));
	g_bEnableAddBan     = SB_GetConfigValue("Addban") == 1;
	g_bEnableUnban      = SB_GetConfigValue("Unban")  == 1;
	g_iProcessQueueTime = SB_GetConfigValue("ProcessQueueTime");
	g_flRetryTime       = float(SB_GetConfigValue("RetryTime"));
	g_hBanTimes         = Handle:SB_GetConfigValue("BanTimes");
	g_hBanTimesFlags    = Handle:SB_GetConfigValue("BanTimesFlags");
	g_hBanTimesLength   = Handle:SB_GetConfigValue("BanTimesLength");
	
	// Get reasons from SourceBans config and store them locally
	decl String:sReason[128];
	new Handle:hBanReasons     = Handle:SB_GetConfigValue("BanReasons");
	new Handle:hHackingReasons = Handle:SB_GetConfigValue("HackingReasons");
	
	// Empty reason menus
	RemoveAllMenuItems(g_hReasonMenu);
	RemoveAllMenuItems(g_hHackingMenu);
	
	// Add reasons from SourceBans config to reason menus
	for(new i = 0, iSize = GetArraySize(hBanReasons);     i < iSize; i++)
	{
		GetArrayString(hBanReasons,     i, sReason, sizeof(sReason));
		AddMenuItem(g_hReasonMenu,  sReason, sReason);
	}
	for(new i = 0, iSize = GetArraySize(hHackingReasons); i < iSize; i++)
	{
		GetArrayString(hHackingReasons, i, sReason, sizeof(sReason));
		AddMenuItem(g_hHackingMenu, sReason, sReason);
	}
	
	// Restart process queue timer
	if(g_hProcessQueue)
		KillTimer(g_hProcessQueue);
	
	g_hProcessQueue = CreateTimer(g_iProcessQueueTime * 60.0, Timer_ProcessQueue, _, TIMER_REPEAT);
}


/**
 * Commands
 */
public Action:Command_Ban(client, args)
{
	if(args < 2)
	{
		ReplyToCommand(client, "%sUsage: sm_ban <#userid|name> <time|0> [reason]", SB_PREFIX);
		return Plugin_Handled;
	}
	
	decl iLen, String:sArg[256], String:sKickMessage[128], String:sTarget[64], String:sTime[12];
	GetCmdArgString(sArg, sizeof(sArg));
	iLen  = BreakString(sArg,       sTarget, sizeof(sTarget));
	iLen += BreakString(sArg[iLen], sTime,   sizeof(sTime));
	
	new iTarget = FindTarget(client, sTarget, true), iTime = StringToInt(sTime);
	if(iTarget == -1)
		return Plugin_Handled;
	
	if(!g_bPlayerStatus[iTarget])
	{
		ReplyToCommand(client, "%s%t", SB_PREFIX, "Ban Not Verified");
		return Plugin_Handled;
	}
	
	Format(sKickMessage, sizeof(sKickMessage), "%T", "Banned Check Site", iTarget, g_sWebsite);
	BanClient(iTarget, iTime, BANFLAG_AUTO, sArg[iLen], sKickMessage, "sm_ban", client);
	return Plugin_Handled;
}

public Action:Command_BanIp(client, args)
{
	if(args < 2)
	{
		ReplyToCommand(client, "%sUsage: sm_banip <ip|#userid|name> <time> [reason]", SB_PREFIX);
		return Plugin_Handled;
	}
	
	decl iLen, iTargets[1], bool:tn_is_ml, String:sArg[256], String:sIp[16], String:sTargets[MAX_TARGET_LENGTH], String:sTime[12];
	GetCmdArgString(sArg, sizeof(sArg));
	iLen  = BreakString(sArg,       sIp,   sizeof(sIp));
	iLen += BreakString(sArg[iLen], sTime, sizeof(sTime));
	
	if(StrEqual(sIp, "0"))
	{
		ReplyToCommand(client, "%s%t", SB_PREFIX, "Cannot ban that IP");
		return Plugin_Handled;
	}
	
	new iTarget = -1, iTime = StringToInt(sTime);
	if(ProcessTargetString(sIp,
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

public Action:Command_AddBan(client, args)
{
	if(args < 2)
	{
		ReplyToCommand(client, "%sUsage: sm_addban <time> <steamid> [reason]", SB_PREFIX);
		return Plugin_Handled;
	}
	if(!g_bEnableAddBan)
	{
		ReplyToCommand(client, "%s%t", SB_PREFIX, "Can Not Add Ban", g_sWebsite);
		return Plugin_Handled;
	}
	
	decl iLen, String:sArg[256], String:sAuth[20], String:sTime[20];
	GetCmdArgString(sArg, sizeof(sArg));
	iLen  = BreakString(sArg,       sTime, sizeof(sTime));
	iLen += BreakString(sArg[iLen], sAuth, sizeof(sAuth));
	
	if(strncmp(sAuth, "STEAM_", 6) != 0 || sAuth[7] != ':')
	{
		ReplyToCommand(client, "%s%t", SB_PREFIX, "Invalid SteamID specified");
		return Plugin_Handled;
	}
	
	new iTime = StringToInt(sTime);
	
	BanIdentity(sAuth, iTime, BANFLAG_AUTHID, sArg[iLen], "sm_addban", client);
	return Plugin_Handled;
}

public Action:Command_Unban(client, args)
{
	if(args < 1)
	{
		ReplyToCommand(client, "%sUsage: sm_unban <steamid|ip>", SB_PREFIX);
		return Plugin_Handled;
	}
	if(!g_bEnableUnban)
	{
		ReplyToCommand(client, "%s%t", SB_PREFIX, "Can Not Unban", g_sWebsite);
		return Plugin_Handled;
	}
	
	decl String:sArg[24];
	GetCmdArgString(sArg, sizeof(sArg));
	StripQuotes(sArg);
	TrimString(sArg);
	
	RemoveBan(sArg, strncmp(sArg, "STEAM_", 6) == 0 && sArg[7] == ':' ? BANFLAG_AUTHID : BANFLAG_IP, "sm_unban", client);
	return Plugin_Handled;
}

public Action:Command_Say(client, const String:command[], argc)
{
	// If this client is not typing their own reason to ban someone, ignore
	if(argc < 1 || !g_bOwnReason[client])
		return Plugin_Continue;
	
	g_bOwnReason[client] = false;
	
	decl String:sText[192];
	GetCmdArgString(sText, sizeof(sText));
	StripQuotes(sText);
	TrimString(sText);
	
	if(!sText[0] || StrEqual(sText[1], "noreason", false))
	{
		ReplyToCommand(client, "%s%t", SB_PREFIX, "Chat Reason Aborted");
		return Plugin_Handled;
	}
	if(g_iBanTarget[client] != -1)
	{
		decl String:sKickMessage[128];
		Format(sKickMessage, sizeof(sKickMessage), "%T", "Banned Check Site", g_iBanTarget[client], g_sWebsite);
		BanClient(g_iBanTarget[client], g_iBanTime[client], BANFLAG_AUTO, sText, sKickMessage, "sm_ban", client);
		return Plugin_Handled;
	}
	return Plugin_Continue;
}


/**
 * Events
 */
public Action:Event_PlayerConnect(Handle:event, const String:name[], bool:dontBroadcast)
{
	decl String:sIp[16];
	GetEventString(event, "address", sIp, sizeof(sIp));
	
	// Strip the port
	new iPos = StrContains(sIp, ":");
	if(iPos != -1)
		sIp[iPos] = '\0';
	
	// If the IP address is banned, don't broadcast the event
	if(GetLocalBan(false, "", sIp))
		SetEventBroadcast(event, true);
	
	return Plugin_Continue;
}


/**
 * Timers
 */
public Action:Timer_KickClient(Handle:timer, any:userid)
{
	new iClient = GetClientOfUserId(userid);
	if(!iClient)
		return;
	
	KickClient(iClient, "%t", "Banned Check Site", g_sWebsite);
}

public Action:Timer_PlayerRecheck(Handle:timer, any:userid)
{
	new iClient = GetClientOfUserId(userid);
	if(!iClient)
		return;
	
	if(!g_bPlayerStatus[iClient] && IsClientInGame(iClient) && IsClientAuthorized(iClient))
		OnClientPostAdminCheck(iClient);
	
	g_hPlayerRecheck[iClient] = INVALID_HANDLE;
}

public Action:Timer_ProcessQueue(Handle:timer, any:data)
{
	if(!g_hSQLiteDB)
		return;
	
	new Handle:hQuery = SQL_Query(g_hSQLiteDB, "SELECT type, steam, ip, name, reason, length, admin_id, admin_ip, create_time \
	                                            FROM   sb_bans \
	                                            WHERE  queued = 1");
	if(!hQuery)
		return;
	
	decl iAdminId, iTime, iLength, iType, String:sAdminIp[16], String:sAuth[20], String:sEscapedName[MAX_NAME_LENGTH * 2 + 1],
	     String:sEscapedReason[256], String:sIp[16], String:sName[MAX_NAME_LENGTH + 1], String:sQuery[1024], String:sReason[128];
	while(SQL_FetchRow(hQuery))
	{
		iType    = SQL_FetchInt(hQuery, 0);
		SQL_FetchString(hQuery, 1, sAuth,    sizeof(sAuth));
		SQL_FetchString(hQuery, 2, sIp,      sizeof(sIp));
		SQL_FetchString(hQuery, 3, sName,    sizeof(sName));
		SQL_FetchString(hQuery, 4, sReason,  sizeof(sReason));
		iLength  = SQL_FetchInt(hQuery, 5);
		iAdminId = SQL_FetchInt(hQuery, 6);
		SQL_FetchString(hQuery, 7, sAdminIp, sizeof(sAdminIp));
		iTime    = SQL_FetchInt(hQuery, 8);
		SQL_EscapeString(g_hSQLiteDB, sName,   sEscapedName,   sizeof(sEscapedName));
		SQL_EscapeString(g_hSQLiteDB, sReason, sEscapedReason, sizeof(sEscapedReason));
		
		if(iTime + iLength * 60 <= GetTime())
		{
			DeleteLocalBan(iType == STEAM_BAN_TYPE ? sAuth : sIp);
			continue;
		}
		
		new Handle:hPack = CreateDataPack();
		WritePackString(hPack, iType == STEAM_BAN_TYPE ? sAuth : sIp);
		
		Format(sQuery, sizeof(sQuery), "INSERT INTO {{bans}} (type, steam, ip, name, reason, length, server_id, admin_id, admin_ip, create_time) \
		                                VALUES      (%i, NULLIF('%s', ''), NULLIF('%s', ''), NULLIF('%s', ''), '%s', %i, %i, NULLIF(%i, 0), '%s', %i)",
		                                iType, sAuth, sIp, sEscapedName, sEscapedReason, iLength, g_iServerId, iAdminId, sAdminIp, iTime);
		SB_Query(Query_AddedFromQueue, sQuery, hPack);
	}
	
	CloseHandle(hQuery);
}

public Action:Timer_ProcessTemp(Handle:timer)
{
	if(!g_hSQLiteDB)
		return;
	
	// Delete temporary bans that expired or were added over 5 minutes ago
	decl String:sQuery[1024];
	Format(sQuery, sizeof(sQuery), "DELETE FROM sb_bans \
	                                WHERE       queued = 0 \
	                                  AND       (create_time + length * 60 <= %i OR insert_time + 300 <= %i)",
	                                GetTime(), GetTime());
	SQL_FastQuery(g_hSQLiteDB, sQuery);
}


/**
 * Menu Handlers
 */
public MenuHandler_Ban(Handle:topmenu, TopMenuAction:action, TopMenuObject:object_id, param, String:buffer[], maxlength)
{
	if(action      == TopMenuAction_DisplayOption)
		Format(buffer, maxlength, "%T", "Ban player", param);
	else if(action == TopMenuAction_SelectOption)
		DisplayBanTargetMenu(param);
}

public MenuHandler_Target(Handle:menu, MenuAction:action, param1, param2)
{
	if(action      == MenuAction_Cancel)
	{
		if(param2 == MenuCancel_ExitBack && g_hTopMenu)
			DisplayTopMenu(g_hTopMenu, param1, TopMenuPosition_LastCategory);
	}
	else if(action == MenuAction_End)
		CloseHandle(menu);
	else if(action == MenuAction_Select)
	{
		decl iTarget, String:sInfo[32];
		GetMenuItem(menu, param2, sInfo, sizeof(sInfo));
		if(!(iTarget = GetClientOfUserId(StringToInt(sInfo))))
			PrintToChat(param1, "%s%t", SB_PREFIX, "Player no longer available");
		else if(!CanUserTarget(param1, iTarget))
			PrintToChat(param1, "%s%t", SB_PREFIX, "Unable to target");
		else
		{
			g_iBanTarget[param1] = iTarget;
			DisplayBanTimeMenu(param1);
		}
	}
}

public MenuHandler_Time(Handle:menu, MenuAction:action, param1, param2)
{
	if(action      == MenuAction_Cancel)
	{
		if(param2 == MenuCancel_ExitBack)
			DisplayBanTargetMenu(param1);
	}
	else if(action == MenuAction_End)
		CloseHandle(menu);
	else if(action == MenuAction_Select)
	{
		decl String:sInfo[32];
		GetMenuItem(menu, param2, sInfo, sizeof(sInfo));
		g_iBanTime[param1] = StringToInt(sInfo);
		DisplayMenu(g_hReasonMenu, param1, MENU_TIME_FOREVER);
	}
}

public MenuHandler_Reason(Handle:menu, MenuAction:action, param1, param2)
{
	if(action == MenuAction_Cancel && param2 == MenuCancel_ExitBack)
	{
		if(menu == g_hHackingMenu)
			DisplayMenu(g_hReasonMenu, param1, MENU_TIME_FOREVER);
		else
			DisplayBanTimeMenu(param1);
	}
	if(action != MenuAction_Select)
		return;
	
	decl String:sInfo[64];
	GetMenuItem(menu, param2, sInfo, sizeof(sInfo));
	if(StrEqual(sInfo, "Hacking") && menu == g_hReasonMenu)
	{
		DisplayMenu(g_hHackingMenu, param1, MENU_TIME_FOREVER);
		return;
	}
	if(StrEqual(sInfo, "Own Reason"))
	{
		g_bOwnReason[param1] = true;
		PrintToChat(param1, "%s%t", SB_PREFIX, "Chat Reason");
		return;
	}
	if(g_iBanTarget[param1] != -1)
	{
		decl String:sKickMessage[128];
		Format(sKickMessage, sizeof(sKickMessage), "%T", "Banned Check Site", g_iBanTarget[param1], g_sWebsite);
		BanClient(g_iBanTarget[param1], g_iBanTime[param1], BANFLAG_AUTO, sInfo, sKickMessage, "sm_ban", param1);
	}
	
	g_iBanTarget[param1] = -1;
	g_iBanTime[param1]   = -1;
}


/**
 * Query Callbacks
 */
public Query_BanInsert(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);
	
	decl String:sAdminIp[16], String:sAuth[20], String:sIp[16], String:sName[MAX_NAME_LENGTH + 1], String:sReason[128];
	new iAdmin   = ReadPackCell(pack);
	new iLength  = ReadPackCell(pack);
	ReadPackString(pack, sAuth,    sizeof(sAuth));
	ReadPackString(pack, sIp,      sizeof(sIp));
	ReadPackString(pack, sName,    sizeof(sName));
	ReadPackString(pack, sReason,  sizeof(sReason));
	new iAdminId = ReadPackCell(pack);
	ReadPackString(pack, sAdminIp, sizeof(sAdminIp));
	CloseHandle(pack);
	
	InsertLocalBan(STEAM_BAN_TYPE, sAuth, sIp, sName, sReason, iLength, iAdminId, sAdminIp, GetTime(), !!error[0]);
	if(error[0])
	{
		LogError("Failed to insert the ban into the database: %s", error);
		
		if(ParseClientFromSerial(iAdmin, true))
			ReplyToCommand(iAdmin, "%sFailed to ban %s.", SB_PREFIX, sAuth);
	}
}

public Query_BanIpSelect(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);
	
	decl String:sAdminIp[16], String:sEscapedReason[256], String:sIp[16], String:sQuery[1024], String:sReason[128];
	new iAdmin   = ReadPackCell(pack);
	new iLength  = ReadPackCell(pack);
	ReadPackString(pack, sIp,      sizeof(sIp));
	ReadPackString(pack, sReason,  sizeof(sReason));
	new iAdminId = ReadPackCell(pack);
	ReadPackString(pack, sAdminIp, sizeof(sAdminIp));
	
	new bool:bPrint = ParseClientFromSerial(iAdmin, true);
	
	if(error[0])
	{
		LogError("Failed to retrieve the IP ban from the database: %s", error);
		
		if(bPrint)
			ReplyToCommand(iAdmin, "%sFailed to ban %s.",     SB_PREFIX, sIp);
		
		CloseHandle(pack);
		return;
	}
	if(SQL_GetRowCount(hndl))
	{
		if(bPrint)
			ReplyToCommand(iAdmin, "%s%s is already banned.", SB_PREFIX, sIp);
		
		CloseHandle(pack);
		return;
	}
	
	SB_Escape(sReason, sEscapedReason, sizeof(sEscapedReason));
	Format(sQuery, sizeof(sQuery), "INSERT INTO {{bans}} (type, ip, reason, length, server_id, admin_id, admin_ip, create_time) \
	                                VALUES      (%i, '%s', '%s', %i, %i, NULLIF(%i, 0), '%s', UNIX_TIMESTAMP())",
	                                IP_BAN_TYPE, sIp, sEscapedReason, iLength, g_iServerId, iAdminId, sAdminIp);
	SB_Query(Query_BanIpInsert, sQuery, pack, DBPrio_High);
}

public Query_BanIpInsert(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);
	
	decl String:sAdminIp[30], String:sIp[16], String:sReason[128];
	new iAdmin   = ReadPackCell(pack);
	new iLength  = ReadPackCell(pack);
	ReadPackString(pack, sIp,      sizeof(sIp));
	ReadPackString(pack, sReason,  sizeof(sReason));
	new iAdminId = ReadPackCell(pack);
	ReadPackString(pack, sAdminIp, sizeof(sAdminIp));
	CloseHandle(pack);
	
	new bool:bPrint = ParseClientFromSerial(iAdmin, true);
	
	InsertLocalBan(IP_BAN_TYPE, "", sIp, "", sReason, iLength, iAdminId, sAdminIp, GetTime(), !!error[0]);
	if(error[0])
	{
		LogError("Failed to insert the IP ban into the database: %s", error);
		
		if(bPrint)
			ReplyToCommand(iAdmin, "%sFailed to ban %s.", SB_PREFIX, sIp);
		return;
	}
	if(bPrint)
		ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "Ban added");
}

public Query_AddBanSelect(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);
	
	decl String:sAdminIp[20], String:sAuth[20], String:sEscapedReason[256], String:sQuery[1024], String:sReason[128];
	new iAdmin   = ReadPackCell(pack);
	new iLength  = ReadPackCell(pack);
	ReadPackString(pack, sAuth,    sizeof(sAuth));
	ReadPackString(pack, sReason,  sizeof(sReason));
	new iAdminId = ReadPackCell(pack);
	ReadPackString(pack, sAdminIp, sizeof(sAdminIp));
	
	new bool:bPrint = ParseClientFromSerial(iAdmin, true);
	
	if(error[0])
	{
		LogError("Failed to retrieve the ID ban from the database: %s", error);
		
		if(bPrint)
			ReplyToCommand(iAdmin, "%sFailed to ban %s.",     SB_PREFIX, sAuth);
		
		CloseHandle(pack);
		return;
	}
	if(SQL_GetRowCount(hndl))
	{
		if(bPrint)
			ReplyToCommand(iAdmin, "%s%s is already banned.", SB_PREFIX, sAuth);
		
		CloseHandle(pack);
		return;
	}
	
	SB_Escape(sReason, sEscapedReason, sizeof(sEscapedReason));
	Format(sQuery, sizeof(sQuery), "INSERT INTO {{bans}} (type, steam, reason, length, server_id, admin_id, admin_ip, create_time) \
	                                VALUES      (%i, '%s', '%s', %i, %i, NULLIF(%i, 0), '%s', UNIX_TIMESTAMP())",
	                                STEAM_BAN_TYPE, sAuth, sEscapedReason, iLength, g_iServerId, iAdminId, sAdminIp);
	SB_Query(Query_AddBanInsert, sQuery, pack, DBPrio_High);
}

public Query_AddBanInsert(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);
	
	decl String:sAdminIp[20], String:sAuth[20], String:sReason[128];
	new iAdmin   = ReadPackCell(pack);
	new iLength  = ReadPackCell(pack);
	ReadPackString(pack, sAuth,    sizeof(sAuth));
	ReadPackString(pack, sReason,  sizeof(sReason));
	new iAdminId = ReadPackCell(pack);
	ReadPackString(pack, sAdminIp, sizeof(sAdminIp));
	CloseHandle(pack);
	
	new bool:bPrint = ParseClientFromSerial(iAdmin, true);
	
	InsertLocalBan(STEAM_BAN_TYPE, sAuth, "", "", sReason, iLength, iAdminId, sAdminIp, GetTime(), !!error[0]);
	if(error[0])
	{
		LogError("Failed to insert the ID ban into the database: %s", error);
		
		if(bPrint)
			ReplyToCommand(iAdmin, "%sFailed to ban %s.", SB_PREFIX, sAuth);
		return;
	}
	if(bPrint)
		ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "Ban added");
}

public Query_UnbanSelect(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);
	
	decl String:sIdentity[20], String:sQuery[1024];
	new iAdmin = ReadPackCell(pack);
	ReadPackString(pack, sIdentity, sizeof(sIdentity));
	
	new bool:bPrint = ParseClientFromSerial(iAdmin, true);
	
	if(error[0])
	{
		LogError("Failed to retrieve the ban from the database: %s", error);
		
		if(bPrint)
			ReplyToCommand(iAdmin, "%sFailed to unban %s.",          SB_PREFIX, sIdentity);
		
		CloseHandle(pack);
		return;
	}
	if(!SQL_GetRowCount(hndl))
	{
		if(bPrint)
			ReplyToCommand(iAdmin, "%sNo active bans found for %s.", SB_PREFIX, sIdentity);
		
		CloseHandle(pack);
		return;
	}
	
	if(strncmp(sIdentity, "STEAM_", 6) == 0)
		Format(sQuery, sizeof(sQuery), "UPDATE   {{bans}} \
		                                SET      unban_admin_id = %i, \
		                                         unban_time     = UNIX_TIMESTAMP() \
		                                WHERE    type           = %i \
		                                  AND    steam          REGEXP '^STEAM_[0-9]:%s$' \
		                                ORDER BY create_time DESC \
		                                LIMIT    1",
		                                GetAdminId(iAdmin), STEAM_BAN_TYPE, sIdentity[8]);
	else
		Format(sQuery, sizeof(sQuery), "UPDATE   {{bans}} \
		                                SET      unban_admin_id = %i, \
		                                         unban_time     = UNIX_TIMESTAMP() \
		                                WHERE    type           = %i \
		                                  AND    ip             = '%s' \
		                                ORDER BY create_time DESC \
		                                LIMIT    1",
		                                GetAdminId(iAdmin), IP_BAN_TYPE, sIdentity);
	
	SB_Query(Query_UnbanUpdate, sQuery, pack, DBPrio_High);
	
	DeleteLocalBan(sIdentity);
}

public Query_UnbanUpdate(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);
	
	decl String:sIdentity[20];
	new iAdmin = ReadPackCell(pack);
	ReadPackString(pack, sIdentity, sizeof(sIdentity));
	CloseHandle(pack);
	
	new bool:bPrint = ParseClientFromSerial(iAdmin, true);
	
	if(error[0])
	{
		LogError("Failed to unban the ban from the database: %s", error);
		
		if(bPrint)
			ReplyToCommand(iAdmin, "%sFailed to unban %s.", SB_PREFIX, sIdentity);
		return;
	}
	if(bPrint)
		ReplyToCommand(iAdmin, "%s%t", SB_PREFIX, "Removed bans matching", sIdentity);
}

public Query_SubmitBan(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);
	
	new iAdmin  = ReadPackCell(pack);
	new iTarget = ReadPackCell(pack);
	CloseHandle(pack);
	
	new bool:bPrint = ParseClientFromSerial(iAdmin, true);
	
	if(error[0])
	{
		LogError("Failed to submit the ban to the database: %s", error);
		
		if(bPrint)
		{
			if(ParseClientFromSerial(iTarget))
				ReplyToCommand(iAdmin, "%sFailed to submit %N.", SB_PREFIX, iTarget);
			else
				ReplyToCommand(iAdmin, "%sFailed to submit.", SB_PREFIX);
		}
		return;
	}
}

public Query_BanVerify(Handle:owner, Handle:hndl, const String:error[], any:iClient)
{
	if(!ParseClientFromSerial(iClient))
		return;
	
	if(error[0])
	{
		LogError("Failed to verify the ban: %s", error);
		
		g_hPlayerRecheck[iClient] = CreateTimer(g_flRetryTime, Timer_PlayerRecheck, ParseClientSerial(iClient));
		return;
	}
	if(!SQL_FetchRow(hndl))
	{
		g_bPlayerStatus[iClient] = true;
		return;
	}
	
	decl String:sAdminIp[16], String:sAuth[20], String:sEscapedName[MAX_NAME_LENGTH * 2 + 1], String:sIp[16],
	     String:sName[MAX_NAME_LENGTH + 1], String:sQuery[1024], String:sReason[128];
	GetClientAuthString(iClient, sAuth, sizeof(sAuth));
	GetClientIP(iClient,         sIp,   sizeof(sIp));
	GetClientName(iClient,       sName, sizeof(sName));
	
	SB_Escape(sName, sEscapedName, sizeof(sEscapedName));
	Format(sQuery, sizeof(sQuery), "INSERT INTO {{blocks}} (ban_id, name, server_id, create_time) \
	                                VALUES      ((SELECT id FROM {{bans}} WHERE ((type = %i AND steam REGEXP '^STEAM_[0-9]:%s$') OR (type = %i AND '%s' REGEXP REPLACE(REPLACE(ip, '.', '\\.') , '.0', '..{1,3}'))) AND unban_time IS NULL ORDER BY create_time LIMIT 1), '%s', %i, UNIX_TIMESTAMP())",
	                                STEAM_BAN_TYPE, sAuth[8], IP_BAN_TYPE, sIp, sEscapedName, g_iServerId);
	SB_Execute(sQuery, DBPrio_High);
	
	// SELECT type, steam, ip, name, reason, length, admin_id, admin_ip, create_time
	new iType    = SQL_FetchInt(hndl, 0);
	SQL_FetchString(hndl, 1, sAuth,    sizeof(sAuth));
	SQL_FetchString(hndl, 2, sIp,      sizeof(sIp));
	SQL_FetchString(hndl, 3, sName,    sizeof(sName));
	SQL_FetchString(hndl, 4, sReason,  sizeof(sReason));
	new iLength  = SQL_FetchInt(hndl, 5);
	new iAdminId = SQL_FetchInt(hndl, 6);
	SQL_FetchString(hndl, 7, sAdminIp, sizeof(sAdminIp));
	new iTime    = SQL_FetchInt(hndl, 8);
	
	PrintBan(iClient, sAuth, sIp, sName, sReason, iLength, iTime);
	
	InsertLocalBan(iType, sAuth, sIp, sName, sReason, iLength, iAdminId, sAdminIp, iTime);
	// Delay kick, otherwise ban information will not be printed to console
	CreateTimer(1.0, Timer_KickClient, GetClientUserId(iClient));
}

public Query_AddedFromQueue(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	decl String:sIdentity[20];
	ResetPack(pack);
	ReadPackString(pack, sIdentity, sizeof(sIdentity));
	CloseHandle(pack);
	
	if(!error[0])
		DeleteLocalBan(sIdentity);
}


/**
 * Natives
 */
public Native_SubmitBan(Handle:plugin, numParams)
{
	decl String:sReason[256];
	new iClient = GetNativeCell(1);
	new iTarget = GetNativeCell(2);
	GetNativeString(3, sReason, sizeof(sReason));
	
	decl String:sEscapedName[MAX_NAME_LENGTH * 2 + 1], String:sEscapedReason[512], String:sEscapedTargetName[MAX_NAME_LENGTH * 2 + 1],
	     String:sIp[16], String:sName[MAX_NAME_LENGTH + 1], String:sQuery[1024], String:sTargetAuth[20], String:sTargetIp[16], String:sTargetName[MAX_NAME_LENGTH + 1];
	GetClientAuthString(iTarget, sTargetAuth, sizeof(sTargetAuth));
	GetClientIP(iClient,         sIp,         sizeof(sIp));
	GetClientIP(iTarget,         sTargetIp,   sizeof(sTargetIp));
	GetClientName(iClient,       sName,       sizeof(sName));
	GetClientName(iTarget,       sTargetName, sizeof(sTargetName));
	
	new Handle:hPack = CreateDataPack();
	WritePackCell(hPack,   ParseClientSerial(iClient));
	WritePackCell(hPack,   ParseClientSerial(iTarget));
	WritePackString(hPack, sReason);
	
	SB_Escape(sName,       sEscapedName,       sizeof(sEscapedName));
	SB_Escape(sReason,     sEscapedReason,     sizeof(sEscapedReason));
	SB_Escape(sTargetName, sEscapedTargetName, sizeof(sEscapedTargetName));
	Format(sQuery, sizeof(sQuery), "INSERT INTO {{submissions}} (name, steam, ip, reason, server_id, user_name, user_ip, create_time) \
	                                VALUES      ('%s', '%s', '%s', '%s', %i, '%s', '%s', UNIX_TIMESTAMP())",
	                                sEscapedTargetName, sTargetAuth, sTargetIp, sEscapedReason, g_iServerId, sEscapedName, sIp);
	SB_Query(Query_SubmitBan, sQuery, hPack);
	
	return SP_ERROR_NONE;
}


/**
 * Stocks
 */
DeleteLocalBan(const String:sIdentity[])
{
	if(!g_hSQLiteDB)
		return;
	
	decl String:sQuery[1024];
	Format(sQuery, sizeof(sQuery), "DELETE FROM sb_bans \
	                                WHERE       (type = %i AND steam = '%s') \
	                                   OR       (type = %i AND ip    = '%s')",
	                                STEAM_BAN_TYPE, sIdentity, IP_BAN_TYPE, sIdentity);
	SQL_FastQuery(g_hSQLiteDB, sQuery);
}

DisplayBanTargetMenu(client)
{
	decl String:sTitle[128];
	new Handle:hMenu = CreateMenu(MenuHandler_Target);
	Format(sTitle, sizeof(sTitle), "%T:", "Ban player", client);
	SetMenuTitle(hMenu, sTitle);
	SetMenuExitBackButton(hMenu, true);
	AddTargetsToMenu2(hMenu, client, COMMAND_FILTER_NO_BOTS|COMMAND_FILTER_CONNECTED);
	DisplayMenu(hMenu, client, MENU_TIME_FOREVER);
}

DisplayBanTimeMenu(client)
{
	decl String:sTitle[128];
	new Handle:hMenu = CreateMenu(MenuHandler_Time);
	Format(sTitle, sizeof(sTitle), "%T:", "Ban player", client);
	SetMenuTitle(hMenu, sTitle);
	SetMenuExitBackButton(hMenu, true);
	
	decl iFlags, String:sFlags[32], String:sLength[16], String:sName[32];
	for(new i = 0, iSize = GetArraySize(g_hBanTimes); i < iSize; i++)
	{
		GetArrayString(g_hBanTimes,       i, sName,   sizeof(sName));
		GetArrayString(g_hBanTimesFlags,  i, sFlags,  sizeof(sFlags));
		GetArrayString(g_hBanTimesLength, i, sLength, sizeof(sLength));
		iFlags = ReadFlagString(sFlags);
		
		if((GetUserFlagBits(client) & iFlags) == iFlags)
			AddMenuItem(hMenu, sLength, sName);
	}
	
	DisplayMenu(hMenu, client, MENU_TIME_FOREVER);
}

GetAdminId(client)
{
	// If admins are enabled, return their admin id, otherwise return 0
	return SB_GetConfigValue("EnableAdmins") ? SB_GetAdminId(client) : 0;
}

bool:GetLocalBan(bool:bType, const String:sAuth[], const String:sIp[] = "", String:sBanAuth[20] = "", String:sBanIp[16] = "", String:sBanName[MAX_NAME_LENGTH + 1] = "", String:sBanReason[128] = "", &iBanLength = 0, &iBanTime = 0)
{
	if(!g_hSQLiteDB)
		return false;
	
	decl String:sQuery[1024];
	if(bType)
		Format(sQuery, sizeof(sQuery), "SELECT steam, ip, name, reason, length, create_time \
		                                FROM   sb_bans \
		                                WHERE  ((type = %i AND steam = '%s') OR (type = %i AND ip = '%s')) \
		                                  AND  (length = 0 OR create_time + length * 60 > %i OR (queued = 0 AND insert_time + 300 > %i))",
		                                STEAM_BAN_TYPE, sAuth[0] ? sAuth : "none", IP_BAN_TYPE, sIp[0] ? sIp : "none", GetTime(), GetTime());
	else
		Format(sQuery, sizeof(sQuery), "SELECT steam, ip, name, reason, length, create_time \
		                                FROM   sb_bans \
		                                WHERE  (steam = '%s' OR ip = '%s') \
		                                  AND  (length = 0 OR create_time + length * 60 > %i OR (queued = 0 AND insert_time + 300 > %i))",
		                                sAuth[0] ? sAuth : "none", sIp[0] ? sIp : "none", GetTime(), GetTime());
	
	new Handle:hQuery = SQL_Query(g_hSQLiteDB, sQuery);
	if(!hQuery)
		return false;
	
	new bool:bResult = SQL_FetchRow(hQuery);
	if(bResult)
	{
		SQL_FetchString(hQuery, 0, sBanAuth,   sizeof(sBanAuth));
		SQL_FetchString(hQuery, 1, sBanIp,     sizeof(sBanIp));
		SQL_FetchString(hQuery, 2, sBanName,   sizeof(sBanName));
		SQL_FetchString(hQuery, 3, sBanReason, sizeof(sBanReason));
		iBanLength = SQL_FetchInt(hQuery, 4);
		iBanTime   = SQL_FetchInt(hQuery, 5);
	}
	
	CloseHandle(hQuery);
	return bResult;
}

InsertLocalBan(iType, const String:sAuth[], const String:sIp[], const String:sName[], const String:sReason[], iLength, iAdminId, const String:sAdminIp[], iTime, bool:bQueued = false)
{
	decl String:sEscapedName[MAX_NAME_LENGTH * 2 + 1], String:sEscapedReason[256], String:sQuery[1024];
	SQL_EscapeString(g_hSQLiteDB, sName,   sEscapedName,   sizeof(sEscapedName));
	SQL_EscapeString(g_hSQLiteDB, sReason, sEscapedReason, sizeof(sEscapedReason));
	
	Format(sQuery, sizeof(sQuery), "INSERT INTO sb_bans (type, steam, ip, name, reason, length, admin_id, admin_ip, queued, create_time, insert_time) \
	                                VALUES      (%i, '%s', '%s', '%s', '%s', %i, %i, '%s', %i, %i, %i)",
	                                iType, sAuth, sIp, sEscapedName, sEscapedReason, iLength, iAdminId, sAdminIp, bQueued ? 1 : 0, iTime, GetTime());
	SQL_FastQuery(g_hSQLiteDB, sQuery);
	
	#if defined _DEBUG
	PrintToServer("%sAdded local ban (%i,%s,%s,%s,%s,%i,%i,%s,%i,%i)", SB_PREFIX, iType, sAuth, sIp, sName, sReason, iLength, iAdminId, sAdminIp, iTime, bQueued ? 1 : 0);
	#endif
}

PrintBan(iClient, const String:sAuth[20], const String:sIp[16], const String:sName[MAX_NAME_LENGTH + 1], const String:sReason[128], iLength, iTime)
{
	PrintToConsole(iClient, "===============================================");
	PrintToConsole(iClient, "%sYou are banned from this server.", SB_PREFIX);
	
	if(iLength)
	{
		decl String:sLength[64];
		SecondsToString(sLength, sizeof(sLength), iTime + (iLength * 60) - GetTime());
		PrintToConsole(iClient, "%sYou have %s left on your ban.",  SB_PREFIX, sLength);
	}
	if(sName[0])
		PrintToConsole(iClient, "%sName:\t\t%s",                    SB_PREFIX, sName);
	if(sAuth[0])
		PrintToConsole(iClient, "%sSteam ID:\t\t%s",                SB_PREFIX, sAuth);
	if(sIp[0])
		PrintToConsole(iClient, "%sIP address:\t%s",                SB_PREFIX, sIp);
	if(sReason[0])
		PrintToConsole(iClient, "%sReason:\t\t%s",                  SB_PREFIX, sReason);
	
	PrintToConsole(iClient, "%sYou can protest your ban at %s.",  SB_PREFIX, g_sWebsite);
	PrintToConsole(iClient, "===============================================");
}

SecondsToString(String:sBuffer[], iLength, iSecs, bool:bTextual = true)
{
	if(bTextual)
	{
		decl String:sDesc[6][8] = {"mo",              "wk",             "d",          "hr",    "min", "sec"};
		new  iCount, iDiv[6]    = {60 * 60 * 24 * 30, 60 * 60 * 24 * 7, 60 * 60 * 24, 60 * 60, 60,    1};
		sBuffer[0]              = '\0';
		
		for(new i = 0; i < sizeof(iDiv); i++)
		{
			if((iCount = iSecs / iDiv[i]) > 0)
			{
				Format(sBuffer, iLength, "%s%i %s, ", sBuffer, iCount, sDesc[i]);
				iSecs %= iDiv[i];
			}
		}
		sBuffer[strlen(sBuffer) - 2] = '\0';
	}
	else
	{
		new iHours = iSecs  / 60 / 60;
		iSecs     -= iHours * 60 * 60;
		new iMins  = iSecs  / 60;
		iSecs     %= 60;
		Format(sBuffer, iLength, "%i:%i:%i", iHours, iMins, iSecs);
	}
}