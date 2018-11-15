<?php

namespace SourceBans\Form;

use SourceBans\Entity\Admin;
use SourceBans\Form\Type\AdminAuthType;
use SourceBans\Form\Type\GroupType;
use SourceBans\Form\Type\OptionalPasswordType;
use SourceBans\Form\Type\ServerGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Name',
            ])
            ->add('auth', AdminAuthType::class, [
                'label' => 'Authentication method',
            ])
            ->add('identity', null, [
                'attr' => [
                    'placeholder' => '[U:1:XXXXXX]',
                ],
                'label' => 'Identity',
            ])
            ->add('email', null, [
                'label' => 'Email address',
                'required' => false,
            ])
            ->add('plainPassword', OptionalPasswordType::class, [
                'label' => 'Password',
                'required' => false,
            ])
            ->add('serverPassword', OptionalPasswordType::class, [
                'label' => 'Server password',
                'required' => false,
            ])
            ->add('group', GroupType::class, [
                'label' => 'Web group',
                'required' => false,
            ])
            ->add('serverGroups', ServerGroupType::class, [
                'label' => 'Server groups',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Admin::class,
        ]);
    }
}
