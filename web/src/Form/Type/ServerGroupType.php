<?php

namespace SourceBans\Form\Type;

use Doctrine\ORM\EntityRepository;
use SourceBans\Entity\ServerGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerGroupType extends AbstractType
{
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

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
