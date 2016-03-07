<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Form\SettingsForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $versionUrl;

    /**
     * @param RouterInterface      $router
     * @param TranslatorInterface  $translator
     * @param FormFactoryInterface $formFactory
     * @param SettingRepository    $settings
     * @param string               $version
     * @param string               $versionUrl
     */
    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        SettingRepository $settings,
        $version,
        $versionUrl
    ) {
        $this->router = $router;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->settings = $settings;
        $this->version = $version;
        $this->versionUrl = $versionUrl;
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

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/version")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function versionAction(Request $request)
    {
        $context = stream_context_create([
            'http' => [
                'user_agent'=> $request->headers->get('User-Agent'),
            ],
        ]);
        $release = @json_decode(file_get_contents($this->versionUrl, false, $context), true);

        if (!isset($release['tag_name'])) {
            return new JsonResponse([
                'error' => $this->translator->trans('controllers.admin.version.error'),
            ]);
        }

        return new JsonResponse([
            'update'  => version_compare($release['tag_name'], $this->version) > 0,
            'url'     => $release['html_url'],
            'version' => $release['tag_name'],
        ]);
    }
}
