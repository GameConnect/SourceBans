<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Form\SettingsForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * DefaultController
 *
 * @Route(service="sourcebans.core.controller.admin.default")
 */
class DefaultController
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
     * @param RouterInterface      $router
     * @param FormFactoryInterface $formFactory
     * @param SettingRepository    $settings
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        SettingRepository $settings
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->settings = $settings;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin")
     * @Security("has_role('ROLE_ADMIN')")
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
     * @Route("/admin/settings")
     * @Security("has_role('ROLE_SETTINGS')")
     * @Template
     */
    public function settingsAction(Request $request)
    {
        $settings = $this->settings->all();

        $form = $this->formFactory->create(SettingsForm::class, $settings)
            ->handleRequest($request);

        if ($form->isValid()) {
            $this->settings->update($form->getData());

            return new RedirectResponse($this->router->generate('sourcebans_core_admin_default_index'));
        }

        return ['form' => $form->createView()];
    }
}
