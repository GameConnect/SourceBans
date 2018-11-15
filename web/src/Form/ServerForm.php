<?php

namespace SourceBans\Form;

use SourceBans\Entity\Server;
use SourceBans\Form\Type\GameType;
use SourceBans\Form\Type\OptionalPasswordType;
use SourceBans\Form\Type\ServerGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host', null, [
                'label' => 'IP address',
            ])
            ->add('port', null, [
                'label' => 'Port',
            ])
            ->add('rconPassword', OptionalPasswordType::class, [
                'label' => 'RCON password',
                'required' => false,
            ])
            ->add('game', GameType::class, [
                'label' => 'Game',
            ])
            ->add('enabled', null, [
                'label' => 'Enabled',
            ])
            ->add('groups', ServerGroupType::class, [
                'label' => 'Server groups',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Server::class,
        ]);
    }
}
