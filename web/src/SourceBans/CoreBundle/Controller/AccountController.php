<?php

namespace SourceBans\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\AdapterInterface;
use SourceBans\CoreBundle\Form\Account\EmailForm;
use SourceBans\CoreBundle\Form\Account\PasswordForm;
use SourceBans\CoreBundle\Form\Account\ServerPasswordForm;
use SourceBans\CoreBundle\Form\Account\SettingsForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * AccountController
 *
 * @Route(service="sourcebans.core.controller.account")
 */
class AccountController
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
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @param RouterInterface       $router
     * @param FormFactoryInterface  $formFactory
     * @param TokenStorageInterface $tokenStorage
     * @param AdapterInterface      $adapter
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        AdapterInterface $adapter
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->adapter = $adapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/account")
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
     * @Route("/account/email")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template
     */
    public function emailAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $form = $this->formFactory->create(EmailForm::class, $user)
            ->handleRequest($request);

        if ($form->isValid()) {
            $this->adapter->persist($user);

            return new RedirectResponse($this->router->generate('sourcebans_core_account_index'));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/account/password")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template
     */
    public function passwordAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $form = $this->formFactory->create(PasswordForm::class, $user)
            ->handleRequest($request);

        if ($form->isValid()) {
            $this->adapter->persist($user);

            return new RedirectResponse($this->router->generate('sourcebans_core_account_index'));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/account/serverPassword")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template
     */
    public function serverPasswordAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $form = $this->formFactory->create(ServerPasswordForm::class, $user)
            ->handleRequest($request);

        if ($form->isValid()) {
            $this->adapter->persist($user);

            return new RedirectResponse($this->router->generate('sourcebans_core_account_index'));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/account/settings")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template
     */
    public function settingsAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $form = $this->formFactory->create(SettingsForm::class, $user)
            ->handleRequest($request);

        if ($form->isValid()) {
            $this->adapter->persist($user);

            return new RedirectResponse($this->router->generate('sourcebans_core_account_index'));
        }

        return ['form' => $form->createView()];
    }
}
