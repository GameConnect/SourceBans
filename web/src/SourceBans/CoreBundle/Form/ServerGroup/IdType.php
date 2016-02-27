<?php

namespace SourceBans\CoreBundle\Form\ServerGroup;

use Doctrine\ORM\EntityRepository;
use SourceBans\CoreBundle\Entity\ServerGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * IdType
 */
class IdType extends AbstractType
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
            'class' => ServerGroup::class,
            'expanded' => true,
            'multiple' => true,
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('serverGroup')
                    ->orderBy('serverGroup.name');
            },
        ]);
    }
}
