<?php

namespace SourceBans\Form\Type;

use Doctrine\ORM\EntityRepository;
use SourceBans\Entity\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Group::class,
            'placeholder' => '- None -',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('webGroup')
                    ->orderBy('webGroup.name');
            },
        ]);
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
