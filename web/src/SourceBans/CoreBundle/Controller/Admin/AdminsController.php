<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\AdminAdapter;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * AdminsController
 *
 * @Route(service="sourcebans.core.controller.admin.admins")
 */
class AdminsController
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var AdminAdapter
     */
    private $adapter;

    /**
     * @param RouterInterface   $router
     * @param SettingRepository $settings
     * @param AdminAdapter      $adapter
     */
    public function __construct(RouterInterface $router, SettingRepository $settings, AdminAdapter $adapter)
    {
        $this->router = $router;
        $this->settings = $settings;
        $this->adapter = $adapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/admins")
     * @Security("has_role('ROLE_VIEW_ADMINS')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $admins = $this->adapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order')
        );

        return ['admins' => $admins];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/admins/add")
     * @Security("has_role('ROLE_ADD_ADMINS')")
     * @Template
     */
    public function addAction(Request $request)
    {
        try {
            $admin = $this->adapter->create();

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_admins_edit', ['id' => $admin->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Request $request
     * @param Admin $admin
     * @return array|Response
     *
     * @Route("/admin/admins/edit/{id}")
     * @Security("has_role('ROLE_EDIT_ADMINS')")
     * @Template
     */
    public function editAction(Request $request, Admin $admin)
    {
        try {
            $this->adapter->update($admin);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_admins_edit', ['id' => $admin->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Admin $admin
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/admins/delete/{id}")
     * @Security("has_role('ROLE_DELETE_ADMINS')")
     */
    public function deleteAction(Admin $admin)
    {
        $this->adapter->delete($admin);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_admins_index'));
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/admins/server/{id}")
     * @Security("has_role('ROLE_VIEW_ADMINS')")
     * @Template
     */
    public function serverAction(Request $request, Server $server)
    {
        return [
            'admins' => $this->adapter->allByServer($server),
            'server' => $server,
        ];
    }
}
