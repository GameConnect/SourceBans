<?php

namespace SourceBans\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Builder
 */
class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav navbar-nav',
            ],
        ]);
        $menu->addChild(
            'controllers.default.dashboard.title',
            ['route' => 'sourcebans_core_default_index']
        );
        $menu->addChild(
            'controllers.default.bans.title',
            ['route' => 'sourcebans_core_bans_index']
        );
        $menu->addChild(
            'controllers.default.servers.title',
            ['route' => 'sourcebans_core_servers_index']
        );
        $menu->addChild(
            'controllers.default.report.title',
            ['route' => 'sourcebans_core_default_report']
        );
        $menu->addChild(
            'controllers.default.appeal.title',
            ['route' => 'sourcebans_core_default_appeal']
        );

        if ($authChecker->isGranted('ROLE_ADMIN')) {
            $menu->addChild(
                'controllers.admin.index.title',
                ['route' => 'sourcebans_core_admin_default_index']
            );
        }

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function userMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav navbar-nav',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_ADMIN')) {
            $menu->addChild(
                'controllers.default.account.title',
                ['route' => 'sourcebans_core_account_index']
            );
            $menu->addChild(
                'controllers.default.logout.title',
                ['route' => 'logout']
            );
        } else {
            $menu->addChild(
                'controllers.default.login.title',
                ['route' => 'sourcebans_core_default_login']
            );
        }

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function accountMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
        ]);
        $menu->addChild(
            'controllers.default.account.menu.permissions',
            ['route' => 'sourcebans_core_account_index']
        );
        $menu->addChild(
            'controllers.default.account.menu.email',
            ['route' => 'sourcebans_core_account_email']
        );
        $menu->addChild(
            'controllers.default.account.menu.password',
            ['route' => 'sourcebans_core_account_password']
        );
        $menu->addChild(
            'controllers.default.account.menu.server-password',
            ['route' => 'sourcebans_core_account_serverpassword']
        );
        $menu->addChild(
            'controllers.default.account.menu.settings',
            ['route' => 'sourcebans_core_account_settings']
        );

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_ADMINS')) {
            $menu->addChild(
                'controllers.admin.admins.title',
                ['route' => 'sourcebans_core_admin_admins_index']
            );
        }
        if ($authChecker->isGranted('ROLE_BANS')) {
            $menu->addChild(
                'controllers.admin.bans.title',
                ['route' => 'sourcebans_core_admin_bans_index']
            );
        }
        if ($authChecker->isGranted('ROLE_GROUPS')) {
            $menu->addChild(
                'controllers.admin.groups.title',
                ['route' => 'sourcebans_core_admin_groups_index']
            );
        }
        if ($authChecker->isGranted('ROLE_SERVERS')) {
            $menu->addChild(
                'controllers.admin.servers.title',
                ['route' => 'sourcebans_core_admin_servers_index']
            );
        }
        if ($authChecker->isGranted('ROLE_GAMES')) {
            $menu->addChild(
                'controllers.admin.games.title',
                ['route' => 'sourcebans_core_admin_games_index']
            );
        }
        if ($authChecker->isGranted('ROLE_SETTINGS')) {
            $menu->addChild(
                'controllers.admin.settings.title',
                ['route' => 'sourcebans_core_admin_default_settings']
            );
        }

        return $menu;
    }
}
