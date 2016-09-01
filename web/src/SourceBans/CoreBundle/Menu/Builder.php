<?php

namespace SourceBans\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use SourceBans\CoreBundle\Entity\Admin;
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
                'class' => 'nav nav-pills nav-justified admin-menu',
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

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function adminAdminsMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_VIEW_ADMINS')) {
            $menu->addChild(
                'controllers.admin.admins.menu.list',
                ['route' => 'sourcebans_core_admin_admins_index']
            );
        }
        if ($authChecker->isGranted('ROLE_ADD_ADMINS')) {
            $menu->addChild(
                'controllers.admin.admins.menu.add',
                ['route' => 'sourcebans_core_admin_admins_add']
            );
            $menu->addChild(
                'controllers.admin.admins.menu.import',
                ['route' => 'sourcebans_core_admin_admins_import']
            );
        }

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function adminBansMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_ADD_BANS')) {
            $menu->addChild(
                'controllers.admin.bans.menu.add',
                ['route' => 'sourcebans_core_admin_bans_add']
            );
            $menu->addChild(
                'controllers.admin.bans.menu.import',
                ['route' => 'sourcebans_core_admin_bans_import']
            );
        }
        if ($authChecker->isGranted('ROLE_APPEALS')) {
            $menu->addChild(
                'controllers.admin.bans.menu.appeals',
                ['route' => 'sourcebans_core_admin_appeals_index']
            );
        }
        if ($authChecker->isGranted('ROLE_REPORTS')) {
            $menu->addChild(
                'controllers.admin.bans.menu.reports',
                ['route' => 'sourcebans_core_admin_reports_index']
            );
        }

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function adminGamesMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_VIEW_GAMES')) {
            $menu->addChild(
                'controllers.admin.games.menu.list',
                ['route' => 'sourcebans_core_admin_games_index']
            );
        }
        if ($authChecker->isGranted('ROLE_ADD_GAMES')) {
            $menu->addChild(
                'controllers.admin.games.menu.add',
                ['route' => 'sourcebans_core_admin_games_add']
            );
            $menu->addChild(
                'controllers.admin.games.menu.map-image',
                ['route' => 'sourcebans_core_admin_games_mapimage']
            );
        }

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function adminGroupsMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_VIEW_GROUPS')) {
            $menu->addChild(
                'controllers.admin.groups.menu.list',
                ['route' => 'sourcebans_core_admin_groups_index']
            );
        }
        if ($authChecker->isGranted('ROLE_ADD_GROUPS')) {
            $menu->addChild(
                'controllers.admin.groups.menu.add',
                ['route' => 'sourcebans_core_admin_groups_add']
            );
            $menu->addChild(
                'controllers.admin.groups.menu.import',
                ['route' => 'sourcebans_core_admin_groups_import']
            );
        }

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     * @return ItemInterface
     */
    public function adminServersMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');
        /** @var Admin $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_VIEW_SERVERS')) {
            $menu->addChild(
                'controllers.admin.servers.menu.list',
                ['route' => 'sourcebans_core_admin_servers_index']
            );
        }
        if ($authChecker->isGranted('ROLE_ADD_SERVERS')) {
            $menu->addChild(
                'controllers.admin.servers.menu.add',
                ['route' => 'sourcebans_core_admin_servers_add']
            );
        }
        if ($user->hasFlag(Admin::FLAG_CONFIG)) {
            $menu->addChild(
                'controllers.servers.config.title',
                ['route' => 'sourcebans_core_admin_servers_config']
            );
        }

        return $menu;
    }
}
