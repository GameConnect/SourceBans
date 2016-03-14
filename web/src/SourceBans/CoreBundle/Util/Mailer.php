<?php

namespace SourceBans\CoreBundle\Util;

use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\SettingRepository;
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
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     * @param \Swift_Mailer       $mailer
     * @param SettingRepository   $settings
     */
    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator,
        \Swift_Mailer $mailer,
        SettingRepository $settings
    ) {
        $this->router = $router;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->settings = $settings;
    }

    /**
     * @param Admin $admin
     */
    public function sendForgotPasswordMail(Admin $admin)
    {
        $link = $this->router->generate('sourcebans_core_account_index', [], RouterInterface::ABSOLUTE_URL);
        $message = \Swift_Message::newInstance()
            ->setFrom($this->settings->get('mailer_from'))
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
        $message = \Swift_Message::newInstance()
            ->setFrom($this->settings->get('mailer_from'))
            ->setTo($admin->getEmail())
            ->setSubject($this->translator->trans('models.LostPasswordForm.reset.subject'))
            ->setBody($this->translator->trans('models.LostPasswordForm.reset.body', [
                '{name}' => $admin->getName(),
                '{link}' => $link,
            ]));
        $this->mailer->send($message);
    }
}
