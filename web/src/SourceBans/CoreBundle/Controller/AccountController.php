<?php

namespace SourceBans\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\AdminAdapter;
use SourceBans\CoreBundle\Form\Account\EmailForm;
use SourceBans\CoreBundle\Form\Account\ForgotPasswordForm;
use SourceBans\CoreBundle\Form\Account\PasswordForm;
use SourceBans\CoreBundle\Form\Account\ServerPasswordForm;
use SourceBans\CoreBundle\Form\Account\SettingsForm;
use SourceBans\CoreBundle\Util\Mailer;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @var Mailer
     */
    private $mailer;

    /**
     * @var AdminAdapter
     */
    private $adapter;

    /**
     * @param RouterInterface       $router
     * @param FormFactoryInterface  $formFactory
     * @param TokenStorageInterface $tokenStorage
     * @param Mailer                $mailer
     * @param AdminAdapter          $adapter
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        Mailer $mailer,
        AdminAdapter $adapter
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->mailer = $mailer;
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
            $this->adapter->update($user);

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
            $this->adapter->update($user);

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
            $this->adapter->update($user);

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
            $this->adapter->update($user);

            return new RedirectResponse($this->router->generate('sourcebans_core_account_index'));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/forgotPassword")
     * @Template
     */
    public function forgotPasswordAction(Request $request)
    {
        $email = $request->query->get('email');
        $validationKey = $request->query->get('key');

        if (!empty($email) && !empty($validationKey)) {
            $admin = $this->adapter->getBy(['email' => $email, 'validationKey' => $validationKey]);

            if ($admin === null) {
                throw new BadRequestHttpException('The validation key does not match the email address for this reset request.');
            }

            $admin->setPlainPassword(bin2hex(random_bytes(4)));
            $this->adapter->update($admin);
            $this->mailer->sendForgotPasswordMail($admin);

            return new RedirectResponse($request->getUri());
        }

        $form = $this->formFactory->create(ForgotPasswordForm::class)
            ->handleRequest($request);

        if ($form->isValid()) {
            $email = $form->get('email')->getData();
            $admin = $this->adapter->getBy(['email' => $email]);

            $admin->setValidationKey(bin2hex(random_bytes(16)));
            $this->adapter->update($admin);
            $this->mailer->sendPasswordResetMail($admin);

            return new RedirectResponse($request->getUri());

        }

        return ['form' => $form->createView()];
    }
}
