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
                Admin::AUTH_STEAM => 'Steam ID',
                Admin::AUTH_IP    => 'IP address',
                Admin::AUTH_NAME  => 'Name',
            ],
        ]);
    }
}
