<?php

namespace SourceBans\CoreBundle\Form\Account;

use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Form\Setting\LanguageType;
use SourceBans\CoreBundle\Form\Setting\ThemeType;
use SourceBans\CoreBundle\Form\Setting\TimezoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SettingsForm
 */
class SettingsForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language', LanguageType::class, [
                'label' => 'models.AccountForm.language',
            ])
            ->add('theme', ThemeType::class, [
                'label' => 'models.AccountForm.theme',
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => 'models.AccountForm.timezone',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Admin::class,
        ]);
    }
}
