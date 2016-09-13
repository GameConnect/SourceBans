<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Rb\Specification\Doctrine\Condition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\AdminAdapter;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\ImportForm;
use SourceBans\CoreBundle\Specification\Admin as AdminSpecification;
use SourceBans\CoreBundle\Util\Admin\ImportFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var AdminAdapter
     */
    private $adapter;

    /**
     * @var ImportFactory
     */
    private $importer;

    /**
     * @param RouterInterface      $router
     * @param FormFactoryInterface $formFactory
     * @param SettingRepository    $settings
     * @param AdminAdapter         $adapter
     * @param ImportFactory        $importer
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        SettingRepository $settings,
        AdminAdapter $adapter,
        ImportFactory $importer
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->settings = $settings;
        $this->adapter = $adapter;
        $this->importer = $importer;
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
            $admin = $this->adapter->create($request);

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
            $this->adapter->update($admin, $request);

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
     * @Route("/admin/admins/import")
     * @Security("has_role('ROLE_ADD_ADMINS')")
     * @Template
     */
    public function importAction(Request $request)
    {
        $form = $this->formFactory->create(ImportForm::class)
            ->handleRequest($request);

        if ($form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $this->importer->import($file->getRealPath(), $file->getClientOriginalName());

            return new RedirectResponse($this->router->generate('sourcebans_core_admin_admins_index'));
        }

        return ['form' => $form->createView()];
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
            'admins' => $this->adapter->allBy([
                new AdminSpecification\Servers,
                new Condition\Equals('id', $server, 'servers'),
            ]),
            'server' => $server,
        ];
    }
}
