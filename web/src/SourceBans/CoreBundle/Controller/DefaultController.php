<?php

namespace SourceBans\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\AdapterInterface;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Exception\InvalidFormException;
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
     * @param AdapterInterface     $appealAdapter
     * @param AdapterInterface     $reportAdapter
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        AuthenticationUtils $authenticationUtils,
        SettingRepository $settings,
        AdapterInterface $appealAdapter,
        AdapterInterface $reportAdapter
    ) {
        $this->httpKernel = $httpKernel;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->authenticationUtils = $authenticationUtils;
        $this->settings = $settings;
        $this->appealAdapter = $appealAdapter;
        $this->reportAdapter = $reportAdapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/")
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
     * @Route("/appeal")
     * @Template
     */
    public function appealAction(Request $request)
    {
        if (!$this->settings->get('enable_appeals')) {
            throw new AccessDeniedHttpException;
        }

        try {
            $this->appealAdapter->create($request);

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
            $this->reportAdapter->create($request);

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
