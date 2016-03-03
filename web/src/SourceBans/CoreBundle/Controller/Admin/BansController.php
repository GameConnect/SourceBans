<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\BanAdapter;
use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * BansController
 *
 * @Route(service="sourcebans.core.controller.admin.bans")
 */
class BansController
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var BanAdapter
     */
    private $adapter;

    /**
     * @param RouterInterface $router
     * @param BanAdapter      $adapter
     */
    public function __construct(RouterInterface $router, BanAdapter $adapter)
    {
        $this->router = $router;
        $this->adapter = $adapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/bans")
     * @Security("has_role('ROLE_ADD_BANS')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/bans/add")
     * @Security("has_role('ROLE_ADD_BANS')")
     * @Template
     */
    public function addAction(Request $request)
    {
        try {
            $ban = $this->adapter->create();

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_bans_edit', ['id' => $ban->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Request $request
     * @param Ban $ban
     * @return array|Response
     *
     * @Route("/admin/bans/edit/{id}")
     * @Security("is_granted('edit', ban)")
     * @Template
     */
    public function editAction(Request $request, Ban $ban)
    {
        try {
            $this->adapter->update($ban);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_bans_edit', ['id' => $ban->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Ban $ban
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/bans/delete/{id}")
     * @Security("has_role('ROLE_DELETE_BANS')")
     */
    public function deleteAction(Ban $ban)
    {
        $this->adapter->delete($ban);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_bans_index'));
    }
}
