<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Form\Setting\DefaultPageType;
use SourceBans\CoreBundle\Form\Setting\LanguageType;
use SourceBans\CoreBundle\Form\Setting\SmtpSecureType;
use SourceBans\CoreBundle\Form\Setting\ThemeType;
use SourceBans\CoreBundle\Form\Setting\TimezoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * SettingsForm
 */
class SettingsForm extends AbstractType
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->host = $requestStack->getCurrentRequest()->server->get('HTTP_HOST');
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $data['bans_hide_admin'] = (bool)$data['bans_hide_admin'];
        $data['bans_hide_ip'] = (bool)$data['bans_hide_ip'];
        $data['bans_public_export'] = (bool)$data['bans_public_export'];
        $data['dashboard_blocks_popup'] = (bool)$data['dashboard_blocks_popup'];
        $data['enable_appeals'] = (bool)$data['enable_appeals'];
        $data['enable_debug'] = (bool)$data['enable_debug'];
        $data['enable_reports'] = (bool)$data['enable_reports'];
        $data['enable_smtp'] = (bool)$data['enable_smtp'];

        $builder
            ->setData($data)
            // General
            ->add('language', LanguageType::class, [
                'label' => 'models.SettingsForm.language',
            ])
            ->add('theme', ThemeType::class, [
                'label' => 'models.SettingsForm.theme',
            ])
            ->add('default_page', DefaultPageType::class, [
                'label' => 'models.SettingsForm.default_page',
            ])
            ->add('enable_reports', CheckboxType::class, [
                'label' => 'models.SettingsForm.enable_reports',
                'required' => false,
            ])
            ->add('enable_appeals', CheckboxType::class, [
                'label' => 'models.SettingsForm.enable_appeals',
                'required' => false,
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => 'models.SettingsForm.timezone',
            ])
            ->add('date_format', TextType::class, [
                'attr' => [
                    'placeholder' => 'm-d-y H:i',
                ],
                'label' => 'models.SettingsForm.date_format',
                'required' => false,
            ])
            ->add('password_min_length', IntegerType::class, [
                'label' => 'models.SettingsForm.password_min_length',
            ])
            ->add('steam_web_api_key', TextType::class, [
                'label' => 'models.SettingsForm.steam_web_api_key',
                'required' => false,
            ])
            // Dashboard
            ->add('dashboard_blocks_popup', CheckboxType::class, [
                'label' => 'models.SettingsForm.dashboard_blocks_popup',
                'required' => false,
            ])
            ->add('dashboard_title', TextType::class, [
                'label' => 'models.SettingsForm.dashboard_title',
                'required' => false,
            ])
            ->add('dashboard_text', TextareaType::class, [
                'label' => false,
                'required' => false,
            ])
            // Bans
            ->add('items_per_page', IntegerType::class, [
                'label' => 'models.SettingsForm.items_per_page',
                'required' => false,
            ])
            ->add('bans_hide_admin', CheckboxType::class, [
                'label' => 'models.SettingsForm.bans_hide_admin',
                'required' => false,
            ])
            ->add('bans_hide_ip', CheckboxType::class, [
                'label' => 'models.SettingsForm.bans_hide_ip',
                'required' => false,
            ])
            ->add('bans_public_export', CheckboxType::class, [
                'label' => 'models.SettingsForm.bans_public_export',
                'required' => false,
            ])
            // Email
            ->add('mailer_from', EmailType::class, [
                'attr' => [
                    'placeholder' => 'noreply@' . $this->host,
                ],
                'label' => 'models.SettingsForm.mailer_from',
                'required' => false,
            ])
            ->add('enable_smtp', CheckboxType::class, [
                'label' => 'models.SettingsForm.enable_smtp',
                'required' => false,
            ])
            ->add('smtp_host', TextType::class, [
                'label' => 'models.SettingsForm.smtp_host',
                'required' => false,
            ])
            ->add('smtp_port', IntegerType::class, [
                'label' => 'models.SettingsForm.smtp_port',
                'required' => false,
            ])
            ->add('smtp_username', TextType::class, [
                'label' => 'models.SettingsForm.smtp_username',
                'required' => false,
            ])
            ->add('smtp_password', PasswordType::class, [
                'label' => 'models.SettingsForm.smtp_password',
                'required' => false,
            ])
            ->add('smtp_secure', SmtpSecureType::class, [
                'label' => 'models.SettingsForm.smtp_secure',
                'expanded' => true,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ]);
    }
}
