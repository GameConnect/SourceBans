<?php

namespace SourceBans\CoreBundle\EventSubscriber;

use Doctrine\Common\Persistence\ManagerRegistry;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\SettingRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

/**
 * LoginSubscriber
 */
class LoginSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $timezone;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @param ManagerRegistry $doctrine
     * @param SettingRepository $settings
     */
    public function __construct(ManagerRegistry $doctrine, SettingRepository $settings)
    {
        $this->doctrine = $doctrine;
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 17],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
            AuthenticationEvents::AUTHENTICATION_SUCCESS => ['onLogin', 0],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        $locale = $request->getSession()->get('_locale');
        $request->setLocale($locale ?: $this->settings->get('language'));

        $timezone = $request->getSession()->get('_timezone');
        date_default_timezone_set($timezone ?: $this->settings->get('timezone'));
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->locale) {
            $event->getRequest()->getSession()->set('_locale', $this->locale);
        }
        if ($this->timezone) {
            $event->getRequest()->getSession()->set('_timezone', $this->timezone);
        }
    }

    /**
     * @param AuthenticationEvent $event
     */
    public function onLogin(AuthenticationEvent $event)
    {
        /** @var Admin $user */
        $user = $event->getAuthenticationToken()->getUser();
        if (!($user instanceof Admin)) {
            return;
        }

        $this->locale = $user->getLanguage();
        $this->timezone = $user->getTimezone();

        $manager = $this->doctrine->getManagerForClass(get_class($user));
        $manager->persist($user->setLoginTime(new \DateTime));
        $manager->flush();
    }
}
