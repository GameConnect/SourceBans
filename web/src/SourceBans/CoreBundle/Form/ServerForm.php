<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Form\Game\IdType as GameType;
use SourceBans\CoreBundle\Form\ServerGroup\IdType as ServerGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ServerForm
 */
class ServerForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host', null, [
                'label' => 'IP address',
            ])
            ->add('port', null, [
                'label' => 'Port',
            ])
            ->add('rcon', OptionalPasswordType::class, [
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

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Server::class,
        ]);
    }
}
