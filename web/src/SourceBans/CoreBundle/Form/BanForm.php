<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Form\Ban\LengthType;
use SourceBans\CoreBundle\Form\Ban\TypeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * BanForm
 */
class BanForm extends AbstractType
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
            ->add('type', TypeType::class, [
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
            ->add('length', LengthType::class, [
                'label' => 'Length',
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
            'data_class' => Ban::class,
        ]);
    }
}
