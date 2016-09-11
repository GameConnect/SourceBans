<?php

namespace SourceBans\CoreBundle\Form\Override;

use SourceBans\CoreBundle\Entity\Override;
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
                'Command' => Override::TYPE_COMMAND,
                'Group'   => Override::TYPE_GROUP,
            ],
        ]);
    }
}
