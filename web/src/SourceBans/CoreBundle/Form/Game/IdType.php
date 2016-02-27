<?php

namespace SourceBans\CoreBundle\Form\Game;

use Doctrine\ORM\EntityRepository;
use SourceBans\CoreBundle\Entity\Game;
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
            'class' => Game::class,
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('game')
                    ->orderBy('game.name');
            },
        ]);
    }
}
