<?php

namespace SourceBans\CoreBundle\EventSubscriber;

use SourceBans\CoreBundle\Event\LoadRolesEvent;
use SourceBans\CoreBundle\Event\RoleEvents;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * RolesSubscriber
 */
class RolesSubscriber implements EventSubscriberInterface
{
    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @param FileLocatorInterface $fileLocator
     */
    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            RoleEvents::LOAD => ['loadRoles', 100],
        ];
    }

    /**
     * @param LoadRolesEvent $event
     */
    public function loadRoles(LoadRolesEvent $event)
    {
        $roles = include $this->fileLocator->locate('@SourceBansCoreBundle/Resources/data/roles.php');

        foreach ($roles as $role) {
            $event->addRole($role);
        }
    }
}
