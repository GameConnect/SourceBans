<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Entity\Override;
use SourceBans\CoreBundle\Form\Override\TypeType;
use SourceBans\CoreBundle\Form\ServerGroup\FlagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * OverrideForm
 */
class OverrideForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TypeType::class, [
                'label' => 'Type',
            ])
            ->add('name', null, [
                'label' => 'Name',
            ])
            ->add('flags', FlagType::class, [
                'label' => 'Server permissions',
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
            'data_class' => Override::class,
        ]);
    }
}
