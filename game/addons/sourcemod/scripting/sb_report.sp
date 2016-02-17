/**
 * SourceBans Report Plugin
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
#include <adminmenu>

#pragma semicolon 1

public Plugin:myinfo =
{
	name        = "SourceBans: Report",
	author      = "GameConnect",
	description = "Allows players to report other players ingame",
	version     = SB_VERSION,
	url         = "http://www.sourcebans.net"
};


/**
 * Globals
 */
enum PlayerData
{
	iReports,
	iReportTarget,
	bool:bIsWaitingForChatReason
};

new g_aPlayers[MAXPLAYERS + 1][PlayerData];
new String:g_sTargetsAuth[MAXPLAYERS + 1][32];
new String:g_sTargetsName[MAXPLAYERS + 1][MAX_NAME_LENGTH + 1];
new String:g_sTargetsIP[MAXPLAYERS + 1][16];
new Handle:g_hReasonMenu;
new Handle:g_hHackingMenu;
new Handle:g_hTopMenu;
new String:g_sWebsite[256];


/**
 * Plugin Forwards
 */
public OnPluginStart()
{
	RegConsoleCmd("sb_report", Command_Report, "sb_report <#userid|name> <reason>");

	LoadTranslations("common.phrases");
	LoadTranslations("sourcebans.phrases");
	LoadTranslations("sb_report.phrases");

	g_hReasonMenu = CreateMenu(MenuHandler_Reason);
	g_hHackingMenu = CreateMenu(MenuHandler_Reason);
}

public OnAdminMenuReady(Handle:topmenu)
{
	if(topmenu != g_hTopMenu)
		g_hTopMenu = topmenu;
}

public OnAllPluginsLoaded()
{
	new Handle:hTopMenu;
	if(LibraryExists("adminmenu") && (hTopMenu = GetAdminTopMenu()))
		OnAdminMenuReady(hTopMenu);
}

public OnLibraryRemoved(const String:name[])
{
	if(StrEqual(name, "adminmenu"))
		g_hTopMenu = INVALID_HANDLE;
}


/**
 * Client Forwards
 */
public OnClientPostAdminCheck(client)
{
	// If it's console or a fake client, or there is no database connection, we can bug out.
	if(!client || IsFakeClient(client) || !SB_IsConnected())
		return;

	// Get the steamid and format the query.
	decl String:sAuth[20], String:sQuery[128];
	GetClientAuthId(client, AuthId_Steam2, sAuth, sizeof(sAuth));
	Format(sQuery, sizeof(sQuery), "SELECT steam FROM {{reports}} WHERE steam REGEXP '^STEAM_[0-9]:%s$'", sAuth[8]);

	// Send the query.
	new Handle:hPack = CreateDataPack();
	WritePackCell(hPack, ParseClientSerial(client));
	WritePackString(hPack, sQuery);
	SB_Query(Query_ReceiveReports, sQuery, hPack, DBPrio_High);
}

public OnClientDisconnect(client)
{
	// Cleanup the client variables
	g_aPlayers[client][iReports] = -1;
	g_aPlayers[client][iReportTarget] = -1;
	// Not going to search to see if the target is currently in the process for a report
	// This allows us to report players even if the person disconnects after the process is started
}


/**
 * SourceBans Forwards
 */
public SB_OnReload()
{
	// Get values from SourceBans config and store them locally
	SB_GetConfigString("Website", g_sWebsite, sizeof(g_sWebsite));

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
	CloseHandle(hBanReasons);
	CloseHandle(hHackingReasons);
}


/**
 * Commands
 */
public Action:Command_Report(client, args)
{
	// Make sure we have arguments, if not, display the player menu and bug out.
	if(!args)
	{
		ReplyToCommand(client, "Usage: sb_report <#userid|name> <reason>");
		DisplayTargetMenu(client);
		return Plugin_Handled;
	}

	// We were at least sent a target, lets check him
	decl String:sTargetBuffer[128];
	GetCmdArg(1, sTargetBuffer, sizeof(sTargetBuffer));
	new iTarget = FindTarget(client, sTargetBuffer, false, false);

	// If it's not a valid target display the player menu and bug out.
	if(iTarget <= 0 || !IsClientInGame(iTarget))
	{
		ReplyToCommand(client, "Usage: sb_report <#userid|name> <reason>");
		DisplayTargetMenu(client);
		return Plugin_Handled;
	}

	// If it's a valid target but the player has already been reported, tell them and bug out.
	if(g_aPlayers[iTarget][iReports])
	{
		decl String:sTargetName[64];
		GetClientName(iTarget, sTargetName, sizeof(sTargetName));
		ReplyToCommand(client, "[SM] %t", "Player already flagged", sTargetName);
		return Plugin_Handled;
	}

	// Set the target variables
	AssignTargetInfo(client, iTarget);

	// If they have given us a reason report the player
	if(args >= 2)
	{
		decl String:sReason[256];
		GetCmdArg(2, sReason, sizeof(sReason));
		ReportPlayer(client, iTarget, sReason);
	}
	// If not, display the reason menu
	else
	{
		ReplyToCommand(client, "Usage: sb_report <#userid|name> <reason>");
		DisplayMenu(g_hReasonMenu, client, MENU_TIME_FOREVER);
	}
	return Plugin_Handled;
}

public Action:OnClientSayCommand(client, const String:command[], const String:sArgs[])
{
	// If this client is not typing their own reason to ban someone, ignore
	if(!sArgs[0] || !g_aPlayers[client][bIsWaitingForChatReason])
		return Plugin_Continue;

	g_aPlayers[client][bIsWaitingForChatReason] = false;

	if (StrEqual(sArgs[1], "abortban", false))
	{
		PrintToChat(client, "%s%t", SB_PREFIX, "Chat Reason Aborted");
		return Plugin_Stop;
	}
	if(g_aPlayers[client][iReportTarget] == -1)
		return Plugin_Continue;

	ReportPlayer(client, g_aPlayers[client][iReportTarget], sArgs);
	return Plugin_Stop;
}


/**
 * Menu Handlers
 */
public MenuHandler_Target(Handle:menu, MenuAction:action, param1, param2)
{
	if(action == MenuAction_Cancel)
	{
		if(param2 == MenuCancel_ExitBack && g_hTopMenu && GetUserFlagBits(param1) & ADMFLAG_GENERIC)
			DisplayTopMenu(g_hTopMenu, param1, TopMenuPosition_LastCategory);
	}
	else if(action == MenuAction_End)
		CloseHandle(menu);
	else if(action == MenuAction_Select)
	{
		decl String:sTargetUserID[10];
		GetMenuItem(menu, param2, sTargetUserID, sizeof(sTargetUserID));
		// Set the target variables
		AssignTargetInfo(param1, GetClientOfUserId(StringToInt(sTargetUserID)));
		DisplayMenu(g_hReasonMenu, param1, MENU_TIME_FOREVER);
	}
}

public MenuHandler_Reason(Handle:menu, MenuAction:action, param1, param2)
{
	if(action == MenuAction_Cancel)
	{
		if(param2 == MenuCancel_ExitBack && g_hTopMenu && GetUserFlagBits(param1) & ADMFLAG_GENERIC)
			DisplayTopMenu(g_hTopMenu, param1, TopMenuPosition_LastCategory);
	}
	else if(action == MenuAction_Select)
	{
		decl String:sInfo[64];
		GetMenuItem(menu, param2, sInfo, sizeof(sInfo));
		if(StrEqual(sInfo, "Hacking") && menu == g_hReasonMenu)
		{
			DisplayMenu(g_hHackingMenu, param1, MENU_TIME_FOREVER);
			return;
		}
		if(StrEqual(sInfo, "Own Reason"))
		{
			g_aPlayers[param1][bIsWaitingForChatReason] = true;
			PrintToChat(param1, "%s%t", SB_PREFIX, "Chat Reason");
			return;
		}
		if(g_aPlayers[param1][iReportTarget] != -1)
		{
			ReportPlayer(param1, g_aPlayers[param1][iReportTarget], sInfo);
		}
	}
}


/**
 * Query Callbacks
 */
public Query_ReceiveReports(Handle:owner, Handle:hndl, const String:error[], any:pack)
{
	ResetPack(pack);

	// If the client is no longer connected we can bug out.
	new iClient = ReadPackCell(pack);
	if(!ParseClientFromSerial(iClient))
	{
		CloseHandle(pack);
		return;
	}

	// Make sure we succeeded.
	if(error[0])
	{
		decl String:sQuery[256];
		ReadPackString(pack, sQuery, sizeof(sQuery));
		LogError("SQL error: %s", error);
		LogError("Query dump: %s", sQuery);
		CloseHandle(pack);
		return;
	}

	// We're done with you now.
	CloseHandle(pack);

	// Set the number of reports
	g_aPlayers[iClient][iReports] = SQL_GetRowCount(hndl);
}


/**
 * Stocks
 */
stock ReportPlayer(client, target, const String:reason[])
{
	SB_ReportPlayer(client, target, reason);

	// Increment the report array for the target.
	g_aPlayers[target][iReports] = 1;

	// Blank out the target for this client
	g_aPlayers[client][iReportTarget] = -1;
}

stock DisplayTargetMenu(client)
{
	new Handle:hMenu = CreateMenu(MenuHandler_Target);
	AddTargetsToMenu(hMenu, 0, true, false);
	SetMenuTitle(hMenu, "Select A Player:");
	SetMenuExitBackButton(hMenu, true);
	DisplayMenu(hMenu, client, MENU_TIME_FOREVER);
}

stock AssignTargetInfo(client, target)
{
	g_aPlayers[client][iReportTarget] = target;
	GetClientAuthId(target, AuthId_Steam2, g_sTargetsAuth[target], sizeof(g_sTargetsAuth[]));
	GetClientIP(target,                    g_sTargetsIP[target],   sizeof(g_sTargetsIP[]));
	GetClientName(target,                  g_sTargetsName[target], sizeof(g_sTargetsName[]));
}
