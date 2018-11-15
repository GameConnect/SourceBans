<?php

namespace SourceBans\Form\Type;

use Doctrine\ORM\EntityRepository;
use SourceBans\Entity\Game;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Game::class,
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('game')
                    ->orderBy('game.name');
            },
        ]);
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
