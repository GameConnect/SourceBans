<?php

namespace SourceBans\Form\Type;

use SourceBans\Entity\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($flagsString) {
                return str_split($flagsString);
            },
            function ($flagsArray) {
                return implode('', $flagsArray);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'Enable all permissions' => Admin::FLAG_ROOT,
                'Reserved slot access' => Admin::FLAG_RESERVATION,
                'Generic admin' => Admin::FLAG_GENERIC,
                'Kick other players' => Admin::FLAG_KICK,
                'Ban other players' => Admin::FLAG_BAN,
                'Remove bans' => Admin::FLAG_UNBAN,
                'Slay/harm other players' => Admin::FLAG_SLAY,
                'Change the map or major gameplay features' => Admin::FLAG_CHANGEMAP,
                'Change most cvars' => Admin::FLAG_CONVARS,
                'Execute config files' => Admin::FLAG_CONFIG,
                'Special chat privileges' => Admin::FLAG_CHAT,
                'Start or create votes' => Admin::FLAG_VOTE,
                'Set a password on the server' => Admin::FLAG_PASSWORD,
                'Use RCON commands' => Admin::FLAG_RCON,
                'Change sv_cheats or use cheating commands' => Admin::FLAG_CHEATS,
                'Custom flag "o"' => Admin::FLAG_CUSTOM1,
                'Custom flag "p"' => Admin::FLAG_CUSTOM2,
                'Custom flag "q"' => Admin::FLAG_CUSTOM3,
                'Custom flag "r"' => Admin::FLAG_CUSTOM4,
                'Custom flag "s"' => Admin::FLAG_CUSTOM5,
                'Custom flag "t"' => Admin::FLAG_CUSTOM6,
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
