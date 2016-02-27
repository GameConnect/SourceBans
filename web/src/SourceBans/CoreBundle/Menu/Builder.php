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
        $translator = $this->container->get('translator');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav navbar-nav',
            ],
        ]);
        $menu->addChild(
            $translator->trans('controllers.default.dashboard.title'),
            ['route' => 'sourcebans_core_default_index']
        );
        $menu->addChild(
            $translator->trans('controllers.default.bans.title'),
            ['route' => 'sourcebans_core_default_bans']
        );
        $menu->addChild(
            $translator->trans('controllers.default.servers.title'),
            ['route' => 'sourcebans_core_default_servers']
        );
        $menu->addChild(
            $translator->trans('controllers.default.report.title'),
            ['route' => 'sourcebans_core_default_report']
        );
        $menu->addChild(
            $translator->trans('controllers.default.appeal.title'),
            ['route' => 'sourcebans_core_default_appeal']
        );

        if ($authChecker->isGranted('ROLE_ADMIN')) {
            $menu->addChild(
                $translator->trans('controllers.admin.index.title'),
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
    public function accountMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');
        $translator = $this->container->get('translator');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav navbar-nav',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_ADMIN')) {
            $menu->addChild(
                $translator->trans('controllers.default.account.title'),
                ['route' => 'sourcebans_core_account_index']
            );
            $menu->addChild(
                $translator->trans('controllers.default.logout.title'),
                ['route' => 'logout']
            );
        } else {
            $menu->addChild(
                $translator->trans('controllers.default.login.title'),
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
    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $authChecker = $this->container->get('security.authorization_checker');
        $translator = $this->container->get('translator');

        $menu = $factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'nav nav-pills nav-justified',
            ],
        ]);

        if ($authChecker->isGranted('ROLE_ADMINS')) {
            $menu->addChild(
                $translator->trans('controllers.admin.admins.title'),
                ['route' => 'sourcebans_core_admin_admins_index']
            );
        }
        if ($authChecker->isGranted('ROLE_BANS')) {
            $menu->addChild(
                $translator->trans('controllers.admin.bans.title'),
                ['route' => 'sourcebans_core_admin_bans_index']
            );
        }
        if ($authChecker->isGranted('ROLE_GROUPS')) {
            $menu->addChild(
                $translator->trans('controllers.admin.groups.title'),
                ['route' => 'sourcebans_core_admin_groups_index']
            );
        }
        if ($authChecker->isGranted('ROLE_SERVERS')) {
            $menu->addChild(
                $translator->trans('controllers.admin.servers.title'),
                ['route' => 'sourcebans_core_admin_servers_index']
            );
        }
        if ($authChecker->isGranted('ROLE_GAMES')) {
            $menu->addChild(
                $translator->trans('controllers.admin.games.title'),
                ['route' => 'sourcebans_core_admin_games_index']
            );
        }
        if ($authChecker->isGranted('ROLE_SETTINGS')) {
            $menu->addChild(
                $translator->trans('controllers.admin.settings.title'),
                ['route' => 'sourcebans_core_admin_default_settings']
            );
        }

        return $menu;
    }
}
