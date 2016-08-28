<?php

namespace SourceBans\CoreBundle\Form\Setting;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SmtpSecureType
 */
class SmtpSecureType extends AbstractType
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
                'None' => '',
                'SSL'  => 'ssl',
                'TLS'  => 'tls',
            ],
        ]);
    }
}
