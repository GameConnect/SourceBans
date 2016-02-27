<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\GroupAdapter;
use SourceBans\CoreBundle\Entity\Group;
use SourceBans\CoreBundle\Exception\InvalidFormException;
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
     * @var GroupAdapter
     */
    private $adapter;

    /**
     * @param RouterInterface $router
     * @param GroupAdapter    $adapter
     */
    public function __construct(RouterInterface $router, GroupAdapter $adapter)
    {
        $this->router = $router;
        $this->adapter = $adapter;
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
            255,
            1,
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
            $group = $this->adapter->create($request->request->all());

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
            $this->adapter->update($group, $request->request->all());

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
}
