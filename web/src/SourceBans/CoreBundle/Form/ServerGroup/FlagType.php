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
                'flags.root'        => Admin::FLAG_ROOT,
                'flags.reservation' => Admin::FLAG_RESERVATION,
                'flags.generic'     => Admin::FLAG_GENERIC,
                'flags.kick'        => Admin::FLAG_KICK,
                'flags.ban'         => Admin::FLAG_BAN,
                'flags.unban'       => Admin::FLAG_UNBAN,
                'flags.slay'        => Admin::FLAG_SLAY,
                'flags.changemap'   => Admin::FLAG_CHANGEMAP,
                'flags.convars'     => Admin::FLAG_CONVARS,
                'flags.config'      => Admin::FLAG_CONFIG,
                'flags.chat'        => Admin::FLAG_CHAT,
                'flags.vote'        => Admin::FLAG_VOTE,
                'flags.password'    => Admin::FLAG_PASSWORD,
                'flags.rcon'        => Admin::FLAG_RCON,
                'flags.cheats'      => Admin::FLAG_CHEATS,
                'flags.custom1'     => Admin::FLAG_CUSTOM1,
                'flags.custom2'     => Admin::FLAG_CUSTOM2,
                'flags.custom3'     => Admin::FLAG_CUSTOM3,
                'flags.custom4'     => Admin::FLAG_CUSTOM4,
                'flags.custom5'     => Admin::FLAG_CUSTOM5,
                'flags.custom6'     => Admin::FLAG_CUSTOM6,
            ],
            'expanded' => true,
            'multiple' => true,
        ]);
    }
}
