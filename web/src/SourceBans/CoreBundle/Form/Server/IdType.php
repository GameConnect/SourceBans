<?php

namespace SourceBans\CoreBundle\Form\Server;

use Doctrine\ORM\EntityRepository;
use SourceBans\CoreBundle\Entity\Server;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * IdType
 */
class IdType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
            'class' => Server::class,
            'group_by' => 'game.name',
            'placeholder' => '- ' . $this->translator->trans('Unknown') . ' -',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('server')
                    ->addSelect('game')
                    ->join('server.game', 'game')
                    ->orderBy('game.name, server.host, server.port');
            },
        ]);
    }
}
