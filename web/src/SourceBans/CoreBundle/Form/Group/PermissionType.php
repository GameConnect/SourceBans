<?php

namespace SourceBans\CoreBundle\Form\Group;

use Doctrine\ORM\EntityRepository;
use SourceBans\CoreBundle\Entity\Permission;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PermissionType
 */
class PermissionType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_translation_domain' => true,
            'class' => Permission::class,
            'expanded' => true,
            'multiple' => true,
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('permission');
            },
        ]);
    }
}
