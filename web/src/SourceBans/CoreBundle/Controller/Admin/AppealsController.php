<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\AppealAdapter;
use SourceBans\CoreBundle\Entity\Appeal;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * AppealsController
 *
 * @Route(service="sourcebans.core.controller.admin.appeals")
 */
class AppealsController
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
     * @var AppealAdapter
     */
    private $adapter;

    /**
     * @param RouterInterface   $router
     * @param SettingRepository $settings
     * @param AppealAdapter     $adapter
     */
    public function __construct(RouterInterface $router, SettingRepository $settings, AppealAdapter $adapter)
    {
        $this->router = $router;
        $this->settings = $settings;
        $this->adapter = $adapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/appeals/{type}", defaults={"type": null})
     * @Security("has_role('ROLE_APPEALS')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $isArchive = $request->query->get('type') == 'archive';

        $appeals = $this->adapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order'),
            [($isArchive ? 'archive' : 'active') => true]
        );

        return ['appeals' => $appeals];
    }

    /**
     * @param Request $request
     * @param Appeal $appeal
     * @return array|Response
     *
     * @Route("/admin/appeals/edit/{id}")
     * @Security("has_role('ROLE_APPEALS')")
     * @Template
     */
    public function editAction(Request $request, Appeal $appeal)
    {
        try {
            $this->adapter->update($appeal, $request);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_appeals_edit', ['id' => $appeal->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Appeal $appeal
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/appeals/delete/{id}")
     * @Security("has_role('ROLE_APPEALS')")
     */
    public function deleteAction(Appeal $appeal)
    {
        $this->adapter->delete($appeal);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_appeals_index'));
    }

    /**
     * @param Appeal $appeal
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/appeals/archive/{id}")
     * @Security("has_role('ROLE_APPEALS')")
     */
    public function archiveAction(Appeal $appeal)
    {
        $this->adapter->archive($appeal);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_appeals_index'));
    }
}
