<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Entity\ServerGroup;
use SourceBans\CoreBundle\Form\ServerGroup\FlagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ServerGroupForm
 */
class ServerGroupForm extends AbstractType
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
            ->add('immunity', null, [
                'label' => 'Immunity level',
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
            'data_class' => ServerGroup::class,
        ]);
    }
}
