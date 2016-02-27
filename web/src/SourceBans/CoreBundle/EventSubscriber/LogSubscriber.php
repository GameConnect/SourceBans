<?php

namespace SourceBans\CoreBundle\EventSubscriber;

use Doctrine\Common\Persistence\ManagerRegistry;
use SourceBans\CoreBundle\Adapter\LogAdapter;
use SourceBans\CoreBundle\Entity\Log;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\AdminAdapterEvent;
use SourceBans\CoreBundle\Event\BanAdapterEvent;
use SourceBans\CoreBundle\Event\GameAdapterEvent;
use SourceBans\CoreBundle\Event\GroupAdapterEvent;
use SourceBans\CoreBundle\Event\ServerAdapterEvent;
use SourceBans\CoreBundle\Event\ServerGroupAdapterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * LogSubscriber
 */
class LogSubscriber implements EventSubscriberInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var LogAdapter
     */
    private $adapter;

    /**
     * @param ManagerRegistry $doctrine
     * @param LogAdapter $adapter
     */
    public function __construct(ManagerRegistry $doctrine, LogAdapter $adapter)
    {
        $this->doctrine = $doctrine;
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AdapterEvents::ADMIN_CREATE        => ['onAdminCreate', 0],
            AdapterEvents::ADMIN_UPDATE        => ['onAdminUpdate', 0],
            AdapterEvents::ADMIN_DELETE        => ['onAdminDelete', 0],
            AdapterEvents::BAN_CREATE          => ['onBanCreate', 0],
            AdapterEvents::BAN_UPDATE          => ['onBanUpdate', 0],
            AdapterEvents::BAN_DELETE          => ['onBanDelete', 0],
            AdapterEvents::GAME_CREATE         => ['onGameCreate', 0],
            AdapterEvents::GAME_UPDATE         => ['onGameUpdate', 0],
            AdapterEvents::GAME_DELETE         => ['onGameDelete', 0],
            AdapterEvents::GROUP_CREATE        => ['onGroupCreate', 0],
            AdapterEvents::GROUP_UPDATE        => ['onGroupUpdate', 0],
            AdapterEvents::GROUP_DELETE        => ['onGroupDelete', 0],
            AdapterEvents::SERVER_CREATE       => ['onServerCreate', 0],
            AdapterEvents::SERVER_UPDATE       => ['onServerUpdate', 0],
            AdapterEvents::SERVER_DELETE       => ['onServerDelete', 0],
            AdapterEvents::SERVER_GROUP_CREATE => ['onServerGroupCreate', 0],
            AdapterEvents::SERVER_GROUP_UPDATE => ['onServerGroupUpdate', 0],
            AdapterEvents::SERVER_GROUP_DELETE => ['onServerGroupDelete', 0],
        ];
    }

    /**
     * @param AdminAdapterEvent $event
     */
    public function onAdminCreate(AdminAdapterEvent $event)
    {
        $this->log('Admin added', 'Admin "' . $event->getAdmin() . '" was added');
    }

    /**
     * @param AdminAdapterEvent $event
     */
    public function onAdminUpdate(AdminAdapterEvent $event)
    {
        $this->log('Admin edited', 'Admin "' . $event->getAdmin() . '" was edited');
    }

    /**
     * @param AdminAdapterEvent $event
     */
    public function onAdminDelete(AdminAdapterEvent $event)
    {
        $this->log('Admin deleted', 'Admin "' . $event->getAdmin() . '" was deleted', Log::TYPE_WARNING);
    }

    /**
     * @param BanAdapterEvent $event
     */
    public function onBanCreate(BanAdapterEvent $event)
    {
        $this->log('Ban added', 'Ban against "' . $event->getBan() . '" was added');
    }

    /**
     * @param BanAdapterEvent $event
     */
    public function onBanUpdate(BanAdapterEvent $event)
    {
        $this->log('Ban edited', 'Ban against "' . $event->getBan() . '" was edited');
    }

    /**
     * @param BanAdapterEvent $event
     */
    public function onBanDelete(BanAdapterEvent $event)
    {
        $this->log('Ban deleted', 'Ban against "' . $event->getBan() . '" was deleted', Log::TYPE_WARNING);
    }

    /**
     * @param GameAdapterEvent $event
     */
    public function onGameCreate(GameAdapterEvent $event)
    {
        $this->log('Game added', 'Game "' . $event->getGame() . '" was added');
    }

    /**
     * @param GameAdapterEvent $event
     */
    public function onGameUpdate(GameAdapterEvent $event)
    {
        $this->log('Game edited', 'Game "' . $event->getGame() . '" was edited');
    }

    /**
     * @param GameAdapterEvent $event
     */
    public function onGameDelete(GameAdapterEvent $event)
    {
        $this->log('Game deleted', 'Game "' . $event->getGame() . '" was deleted', Log::TYPE_WARNING);
    }

    /**
     * @param GroupAdapterEvent $event
     */
    public function onGroupCreate(GroupAdapterEvent $event)
    {
        $this->log('Group added', 'Web group "' . $event->getGroup() . '" was added');
    }

    /**
     * @param GroupAdapterEvent $event
     */
    public function onGroupUpdate(GroupAdapterEvent $event)
    {
        $this->log('Group edited', 'Web group "' . $event->getGroup() . '" was edited');
    }

    /**
     * @param GroupAdapterEvent $event
     */
    public function onGroupDelete(GroupAdapterEvent $event)
    {
        $this->log('Group deleted', 'Web group "' . $event->getGroup() . '" was deleted', Log::TYPE_WARNING);
    }

    /**
     * @param ServerAdapterEvent $event
     */
    public function onServerCreate(ServerAdapterEvent $event)
    {
        $this->log('Server added', 'Server "' . $event->getServer() . '" was added');
    }

    /**
     * @param ServerAdapterEvent $event
     */
    public function onServerUpdate(ServerAdapterEvent $event)
    {
        $this->log('Server edited', 'Server "' . $event->getServer() . '" was edited');
    }

    /**
     * @param ServerAdapterEvent $event
     */
    public function onServerDelete(ServerAdapterEvent $event)
    {
        $this->log('Server deleted', 'Server "' . $event->getServer() . '" was deleted', Log::TYPE_WARNING);
    }

    /**
     * @param ServerGroupAdapterEvent $event
     */
    public function onServerGroupCreate(ServerGroupAdapterEvent $event)
    {
        $this->log('Group added', 'Server group "' . $event->getServerGroup() . '" was added');
    }

    /**
     * @param ServerGroupAdapterEvent $event
     */
    public function onServerGroupUpdate(ServerGroupAdapterEvent $event)
    {
        $this->log('Group edited', 'Server group "' . $event->getServerGroup() . '" was edited');
    }

    /**
     * @param ServerGroupAdapterEvent $event
     */
    public function onServerGroupDelete(ServerGroupAdapterEvent $event)
    {
        $this->log('Group deleted', 'Server group "' . $event->getServerGroup() . '" was deleted', Log::TYPE_WARNING);
    }

    /**
     * @param string $title
     * @param string $message
     * @param string $type
     */
    private function log($title, $message, $type = Log::TYPE_INFORMATION)
    {
        $this->adapter->create([
            'message' => $message,
            'title' => $title,
            'type' => $type,
        ]);
    }
}
