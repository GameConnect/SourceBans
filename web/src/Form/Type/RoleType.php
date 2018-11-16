<?php

namespace SourceBans\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'Root administrator' => 'ROLE_ROOT',
            ],
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
