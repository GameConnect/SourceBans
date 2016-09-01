<?php

namespace SourceBans\CoreBundle\Event;

/**
 * AdapterEvents
 */
final class AdapterEvents
{
    /**
     * This event is dispatched when an admin is created
     */
    const ADMIN_CREATE = 'admin.create';

    /**
     * This event is dispatched when an admin is updated
     */
    const ADMIN_UPDATE = 'admin.update';

    /**
     * This event is dispatched when an admin is deleted
     */
    const ADMIN_DELETE = 'admin.delete';

    /**
     * This event is dispatched when an appeal is created
     */
    const APPEAL_CREATE = 'appeal.create';

    /**
     * This event is dispatched when an appeal is updated
     */
    const APPEAL_UPDATE = 'appeal.update';

    /**
     * This event is dispatched when an appeal is deleted
     */
    const APPEAL_DELETE = 'appeal.delete';

    /**
     * This event is dispatched when an appeal is archived
     */
    const APPEAL_ARCHIVE = 'appeal.archive';

    /**
     * This event is dispatched when a ban is created
     */
    const BAN_CREATE = 'ban.create';

    /**
     * This event is dispatched when a ban is updated
     */
    const BAN_UPDATE = 'ban.update';

    /**
     * This event is dispatched when a ban is deleted
     */
    const BAN_DELETE = 'ban.delete';

    /**
     * This event is dispatched when a ban is unbanned
     */
    const BAN_UNBAN = 'ban.unban';

    /**
     * This event is dispatched when a comment is created
     */
    const COMMENT_CREATE = 'comment.create';

    /**
     * This event is dispatched when a comment is updated
     */
    const COMMENT_UPDATE = 'comment.update';

    /**
     * This event is dispatched when a comment is deleted
     */
    const COMMENT_DELETE = 'comment.delete';

    /**
     * This event is dispatched when a game is created
     */
    const GAME_CREATE = 'game.create';

    /**
     * This event is dispatched when a game is updated
     */
    const GAME_UPDATE = 'game.update';

    /**
     * This event is dispatched when a game is deleted
     */
    const GAME_DELETE = 'game.delete';

    /**
     * This event is dispatched when a web group is created
     */
    const GROUP_CREATE = 'group.create';

    /**
     * This event is dispatched when a web group is updated
     */
    const GROUP_UPDATE = 'group.update';

    /**
     * This event is dispatched when a web group is deleted
     */
    const GROUP_DELETE = 'group.delete';

    /**
     * This event is dispatched when a report is created
     */
    const REPORT_CREATE = 'report.create';

    /**
     * This event is dispatched when a report is updated
     */
    const REPORT_UPDATE = 'report.update';

    /**
     * This event is dispatched when a report is deleted
     */
    const REPORT_DELETE = 'report.delete';

    /**
     * This event is dispatched when a report is archived
     */
    const REPORT_ARCHIVE = 'report.archive';

    /**
     * This event is dispatched when a server is created
     */
    const SERVER_CREATE = 'server.create';

    /**
     * This event is dispatched when a server is updated
     */
    const SERVER_UPDATE = 'server.update';

    /**
     * This event is dispatched when a server is deleted
     */
    const SERVER_DELETE = 'server.delete';

    /**
     * This event is dispatched when a server group is created
     */
    const SERVER_GROUP_CREATE = 'serverGroup.create';

    /**
     * This event is dispatched when a server group is updated
     */
    const SERVER_GROUP_UPDATE = 'serverGroup.update';

    /**
     * This event is dispatched when a server group is deleted
     */
    const SERVER_GROUP_DELETE = 'serverGroup.delete';
}
