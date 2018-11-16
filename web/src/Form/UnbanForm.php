<?php

namespace SourceBans\Form;

use SourceBans\Entity\Ban;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnbanForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unbanReason', TextareaType::class, [
                'label' => 'Reason',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Unban',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ban::class,
        ]);
    }
}
