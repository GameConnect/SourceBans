<?php

namespace SourceBans\Form\Type;

use Doctrine\ORM\EntityRepository;
use SourceBans\Entity\Server;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Server::class,
            'group_by' => 'game.name',
            'placeholder' => '- Unknown -',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('server')
                    ->addSelect('game')
                    ->join('server.game', 'game')
                    ->where('server.enabled = :enabled')
                    ->orderBy('game.name, server.host, server.port')
                    ->setParameter('enabled', true);
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
