<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Form\Admin\AuthType;
use SourceBans\CoreBundle\Form\Group\IdType as GroupType;
use SourceBans\CoreBundle\Form\ServerGroup\IdType as ServerGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AdminForm
 */
class AdminForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Name',
            ])
            ->add('auth', AuthType::class, [
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
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'required' => false,
            ])
            ->add('serverPassword', PasswordType::class, [
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
