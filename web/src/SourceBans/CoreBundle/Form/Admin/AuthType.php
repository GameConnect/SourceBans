<?php

namespace SourceBans\CoreBundle\Form\Admin;

use SourceBans\CoreBundle\Entity\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AuthType
 */
class AuthType extends AbstractType
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
                'Steam ID'   => Admin::AUTH_STEAM,
                'IP address' => Admin::AUTH_IP,
                'Name'       => Admin::AUTH_NAME,
            ],
        ]);
    }
}
