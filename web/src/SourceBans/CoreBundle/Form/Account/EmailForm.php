<?php

namespace SourceBans\CoreBundle\Form\Account;

use SourceBans\CoreBundle\Entity\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * EmailForm
 */
class EmailForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentEmail', EmailType::class, [
                'attr' => [
                    'readonly' => true,
                ],
                'data' => $builder->getData()->getEmail(),
                'label' => 'models.AccountForm.email',
                'mapped' => false,
            ])
            ->add('email', RepeatedType::class, [
                'data' => '',
                'type' => EmailType::class,
                'first_options' => [
                    'label' => 'models.AccountForm.new_email',
                ],
                'second_options' => [
                    'label' => 'models.AccountForm.confirm_email',
                ],
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
