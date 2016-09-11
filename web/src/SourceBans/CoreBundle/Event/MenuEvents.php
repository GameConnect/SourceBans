<?php

namespace SourceBans\CoreBundle\Event;

/**
 * MenuEvents
 */
final class MenuEvents
{
    /**
     * This event is dispatched when the main menu is loaded
     */
    const MAIN_LOAD = 'menu.main.load';

    /**
     * This event is dispatched when the account menu is loaded
     */
    const ACCOUNT_LOAD = 'menu.account.load';

    /**
     * This event is dispatched when the admin/admins menu is loaded
     */
    const ADMIN_ADMINS_LOAD = 'menu.admin.admins.load';

    /**
     * This event is dispatched when the admin/bans menu is loaded
     */
    const ADMIN_BANS_LOAD = 'menu.admin.bans.load';

    /**
     * This event is dispatched when the admin/games menu is loaded
     */
    const ADMIN_GAMES_LOAD = 'menu.admin.games.load';

    /**
     * This event is dispatched when the admin/groups menu is loaded
     */
    const ADMIN_GROUPS_LOAD = 'menu.admin.groups.load';

    /**
     * This event is dispatched when the admin/servers menu is loaded
     */
    const ADMIN_SERVERS_LOAD = 'menu.admin.servers.load';
}
