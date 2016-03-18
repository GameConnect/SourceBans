<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\GroupAdapter;
use SourceBans\CoreBundle\Entity\Group;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\ImportForm;
use SourceBans\CoreBundle\Util\ServerGroup\ImportFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * GroupsController
 *
 * @Route(service="sourcebans.core.controller.admin.groups")
 */
class GroupsController
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
     * @var GroupAdapter
     */
    private $adapter;

    /**
     * @var ImportFactory
     */
    private $importer;

    /**
     * @param RouterInterface      $router
     * @param FormFactoryInterface $formFactory
     * @param GroupAdapter         $adapter
     * @param ImportFactory        $importer
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        GroupAdapter $adapter,
        ImportFactory $importer
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->adapter = $adapter;
        $this->importer = $importer;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/groups")
     * @Security("has_role('ROLE_VIEW_GROUPS')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $groups = $this->adapter->all(
            null,
            null,
            $request->query->get('sort'),
            $request->query->get('order')
        );

        return ['groups' => $groups];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/groups/add")
     * @Security("has_role('ROLE_ADD_GROUPS')")
     * @Template
     */
    public function addAction(Request $request)
    {
        try {
            $group = $this->adapter->create($request);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_groups_edit', ['id' => $group->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Request $request
     * @param Group $group
     * @return array|Response
     *
     * @Route("/admin/groups/edit/{id}")
     * @Security("has_role('ROLE_EDIT_GROUPS')")
     * @Template
     */
    public function editAction(Request $request, Group $group)
    {
        try {
            $this->adapter->update($group, $request);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_groups_edit', ['id' => $group->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Group $group
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/groups/delete/{id}")
     * @Security("has_role('ROLE_DELETE_GROUPS')")
     */
    public function deleteAction(Group $group)
    {
        $this->adapter->delete($group);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_groups_index'));
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/groups/import")
     * @Security("has_role('ROLE_ADD_GROUPS')")
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

            return new RedirectResponse($this->router->generate('sourcebans_core_admin_groups_index'));
        }

        return ['form' => $form->createView()];
    }
}
