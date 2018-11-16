<?php

namespace SourceBans\Form\Type;

use SourceBans\Entity\Ban;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BanLengthType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'Permanent' => Ban::LENGTH_PERMANENT,
                'minutes' => [
                    '1 minute' => 1,
                    '5 minutes' => 5,
                    '10 minutes' => 10,
                    '15 minutes' => 15,
                    '30 minutes' => 30,
                    '45 minutes' => 45,
                ],
                'hours' => [
                    '1 hour' => 60,
                    '2 hours' => 120,
                    '3 hours' => 180,
                    '4 hours' => 240,
                    '8 hours' => 480,
                    '12 hours' => 720,
                ],
                'days' => [
                    '1 day' => 1440,
                    '2 days' => 2880,
                    '3 days' => 4320,
                    '4 days' => 5760,
                    '5 days' => 7200,
                    '6 days' => 8640,
                ],
                'weeks' => [
                    '1 week' => 10080,
                    '2 weeks' => 20160,
                    '3 weeks' => 30240,
                ],
                'months' => [
                    '1 month' => 43200,
                    '2 months' => 86400,
                    '3 months' => 129600,
                    '6 months' => 259200,
                    '12 months' => 518400,
                ],
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
