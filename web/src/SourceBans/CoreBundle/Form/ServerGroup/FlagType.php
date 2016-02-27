<?php

namespace SourceBans\CoreBundle\Form\ServerGroup;

use SourceBans\CoreBundle\Entity\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FlagType
 */
class FlagType extends AbstractType
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
                Admin::FLAG_ROOT        => 'flags.root',
                Admin::FLAG_RESERVATION => 'flags.reservation',
                Admin::FLAG_GENERIC     => 'flags.generic',
                Admin::FLAG_KICK        => 'flags.kick',
                Admin::FLAG_BAN         => 'flags.ban',
                Admin::FLAG_UNBAN       => 'flags.unban',
                Admin::FLAG_SLAY        => 'flags.slay',
                Admin::FLAG_CHANGEMAP   => 'flags.changemap',
                Admin::FLAG_CONVARS     => 'flags.convars',
                Admin::FLAG_CONFIG      => 'flags.config',
                Admin::FLAG_CHAT        => 'flags.chat',
                Admin::FLAG_VOTE        => 'flags.vote',
                Admin::FLAG_PASSWORD    => 'flags.password',
                Admin::FLAG_RCON        => 'flags.rcon',
                Admin::FLAG_CHEATS      => 'flags.cheats',
                Admin::FLAG_CUSTOM1     => 'flags.custom1',
                Admin::FLAG_CUSTOM2     => 'flags.custom2',
                Admin::FLAG_CUSTOM3     => 'flags.custom3',
                Admin::FLAG_CUSTOM4     => 'flags.custom4',
                Admin::FLAG_CUSTOM5     => 'flags.custom5',
                Admin::FLAG_CUSTOM6     => 'flags.custom6',
            ],
            'expanded' => true,
            'multiple' => true,
        ]);
    }
}
