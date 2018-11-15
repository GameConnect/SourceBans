<?php

namespace SourceBans\Form\Type;

use SourceBans\Entity\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminAuthType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'Steam ID' => Admin::AUTH_STEAM,
                'IP address' => Admin::AUTH_IP,
                'Name' => Admin::AUTH_NAME,
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
