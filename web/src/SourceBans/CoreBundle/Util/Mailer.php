<?php

namespace SourceBans\CoreBundle\Util;

use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\SettingRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Mailer
 */
class Mailer
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
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var string
     */
    private $host;

    /**
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     * @param RequestStack        $requestStack
     * @param \Swift_Mailer       $mailer
     * @param SettingRepository   $settings
     */
    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        \Swift_Mailer $mailer,
        SettingRepository $settings
    ) {
        $this->router = $router;
        $this->translator = $translator;
        $this->mailer = ($settings->get('enable_smtp') ? $this->createSmtpMailer() : $mailer);
        $this->settings = $settings;
        $this->host = $requestStack->getCurrentRequest()->server->get('HTTP_HOST');
    }

    /**
     * @param Admin $admin
     */
    public function sendForgotPasswordMail(Admin $admin)
    {
        $link = $this->router->generate('sourcebans_core_account_index', [], RouterInterface::ABSOLUTE_URL);
        $message = $this->createMessage()
            ->setTo($admin->getEmail())
            ->setSubject($this->translator->trans('controllers.default.lostPassword.subject'))
            ->setBody($this->translator->trans('controllers.default.lostPassword.body', [
                '{name}' => $admin->getName(),
                '{password}' => $admin->getPlainPassword(),
                '{link}' => $link,
            ]));
        $this->mailer->send($message);
    }

    /**
     * @param Admin $admin
     */
    public function sendPasswordResetMail(Admin $admin)
    {
        $link = $this->router->generate(
            'sourcebans_core_account_forgotpassword',
            ['email' => $admin->getEmail(), 'key' => $admin->getValidationKey()],
            RouterInterface::ABSOLUTE_URL
        );
        $message = $this->createMessage()
            ->setTo($admin->getEmail())
            ->setSubject($this->translator->trans('models.LostPasswordForm.reset.subject'))
            ->setBody($this->translator->trans('models.LostPasswordForm.reset.body', [
                '{name}' => $admin->getName(),
                '{link}' => $link,
            ]));
        $this->mailer->send($message);
    }

    /**
     * @return \Swift_Message
     */
    private function createMessage()
    {
        return \Swift_Message::newInstance()
            ->setFrom($this->settings->get('mailer_from') ?: 'noreply@' . $this->host);
    }

    /**
     * @return \Swift_Mailer
     */
    private function createSmtpMailer()
    {
        $transport = \Swift_SmtpTransport::newInstance()
            ->setHost($this->settings->get('smtp_host'))
            ->setPort($this->settings->get('smtp_port'))
            ->setEncryption($this->settings->get('smtp_secure'))
            ->setUsername($this->settings->get('smtp_username'))
            ->setPassword($this->settings->get('smtp_password'));

        return \Swift_Mailer::newInstance($transport);
    }
}
