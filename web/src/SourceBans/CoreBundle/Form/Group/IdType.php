<?php

namespace SourceBans\CoreBundle\Form\Group;

use Doctrine\ORM\EntityRepository;
use SourceBans\CoreBundle\Entity\Group;
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
            'class' => Group::class,
            'placeholder' => '- ' . $this->translator->trans('None') . ' -',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('webGroup')
                    ->orderBy('webGroup.name');
            },
        ]);
    }
}
