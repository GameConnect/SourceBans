<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Entity\Group;
use SourceBans\CoreBundle\Form\Group\PermissionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * GroupForm
 */
class GroupForm extends AbstractType
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
            ->add('permissions', PermissionType::class, [
                'label' => 'Web permissions',
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
            'data_class' => Group::class,
        ]);
    }
}
