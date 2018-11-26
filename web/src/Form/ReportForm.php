<?php

namespace SourceBans\Form;

use SourceBans\Entity\Report;
use SourceBans\Form\Type\ServerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('steam', null, [
                'attr' => [
                    'placeholder' => '[U:1:XXXXXX]',
                ],
                'label' => 'Steam ID',
            ])
            ->add('ip', null, [
                'label' => 'IP address',
            ])
            ->add('name', null, [
                'label' => 'Name',
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Reason',
            ])
            ->add('userName', null, [
                'label' => 'Your name',
            ])
            ->add('userEmail', null, [
                'label' => 'Your email address',
            ])
            ->add('server', ServerType::class, [
                'label' => 'Server',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}
