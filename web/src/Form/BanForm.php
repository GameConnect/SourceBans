<?php

namespace SourceBans\Form;

use SourceBans\Entity\Ban;
use SourceBans\Form\Type\BanLengthType;
use SourceBans\Form\Type\BanTypeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BanForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Name',
            ])
            ->add('type', BanTypeType::class, [
                'label' => 'Type',
            ])
            ->add('steam', null, [
                'attr' => [
                    'placeholder' => '[U:1:XXXXXX]',
                ],
                'label' => 'Steam ID',
            ])
            ->add('ip', null, [
                'label' => 'IP address',
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Reason',
            ])
            ->add('length', BanLengthType::class, [
                'label' => 'Length',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ban::class,
        ]);
    }
}
