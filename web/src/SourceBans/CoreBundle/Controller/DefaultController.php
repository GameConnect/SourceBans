<?php

namespace SourceBans\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\AdapterInterface;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\ForgotPasswordForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * DefaultController
 *
 * @Route(service="sourcebans.core.controller.default")
 */
class DefaultController
{
    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var AdapterInterface
     */
    private $banAdapter;

    /**
     * @var AdapterInterface
     */
    private $serverAdapter;

    /**
     * @var AdapterInterface
     */
    private $appealAdapter;

    /**
     * @var AdapterInterface
     */
    private $reportAdapter;

    /**
     * @param HttpKernelInterface  $httpKernel
     * @param TranslatorInterface  $translator
     * @param FormFactoryInterface $formFactory
     * @param AuthenticationUtils  $authenticationUtils
     * @param SettingRepository    $settings
     * @param AdapterInterface     $banAdapter
     * @param AdapterInterface     $serverAdapter
     * @param AdapterInterface     $appealAdapter
     * @param AdapterInterface     $reportAdapter
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        AuthenticationUtils $authenticationUtils,
        SettingRepository $settings,
        AdapterInterface $banAdapter,
        AdapterInterface $serverAdapter,
        AdapterInterface $appealAdapter,
        AdapterInterface $reportAdapter
    ) {
        $this->httpKernel = $httpKernel;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->authenticationUtils = $authenticationUtils;
        $this->settings = $settings;
        $this->banAdapter = $banAdapter;
        $this->serverAdapter = $serverAdapter;
        $this->appealAdapter = $appealAdapter;
        $this->reportAdapter = $reportAdapter;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $defaultPage = $this->settings->get('default_page');
        $controller = 'sourcebans.core.controller.default:' . $defaultPage . 'Action';
        $subRequest = $request->duplicate(null, null, ['_controller' => $controller]);

        return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/dashboard")
     * @Template
     */
    public function dashboardAction(Request $request)
    {
        return [];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/bans/{type}", defaults={"type": null})
     * @Template
     */
    public function bansAction(Request $request)
    {
        $isActive = $request->query->get('type') == 'active';

        $bans = $this->banAdapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order'),
            ['active' => $isActive]
        );

        return ['bans' => $bans];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/servers")
     * @Template
     */
    public function serversAction(Request $request)
    {
        $servers = $this->serverAdapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order')
        );

        return ['servers' => $servers];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/appeal")
     * @Template
     */
    public function appealAction(Request $request)
    {
        if (!$this->settings->get('enable_appeals')) {
            throw new AccessDeniedHttpException;
        }

        try {
            $this->appealAdapter->create($request->request->all());

            $request->getSession()->getFlashBag()->add(
                'success',
                $this->translator->trans('Saved successfully')
            );

            return new RedirectResponse($request->getUri());
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/report")
     * @Template
     */
    public function reportAction(Request $request)
    {
        if (!$this->settings->get('enable_reports')) {
            throw new AccessDeniedHttpException;
        }

        try {
            $this->reportAdapter->create($request->request->all());

            $request->getSession()->getFlashBag()->add(
                'success',
                $this->translator->trans('Saved successfully')
            );

            return new RedirectResponse($request->getUri());
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        return [
            'error'         => $this->authenticationUtils->getLastAuthenticationError(),
            'last_username' => $this->authenticationUtils->getLastUsername(),
        ];
    }
}
