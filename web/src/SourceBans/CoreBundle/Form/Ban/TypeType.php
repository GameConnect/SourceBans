<?php

namespace SourceBans\CoreBundle\Form\Ban;

use SourceBans\CoreBundle\Entity\Ban;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TypeType
 */
class TypeType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'Steam ID'   => Ban::TYPE_STEAM,
                'IP address' => Ban::TYPE_IP,
            ],
        ]);
    }
}
