<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Form\Game\IdType as GameType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * MapImageForm
 */
class MapImageForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('game', GameType::class, [
                'label' => 'Game',
            ])
            ->add('mapName', TextType::class, [
                'label' => 'Map name',
            ])
            ->add('file', FileType::class, [
                'label' => 'File',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Upload',
            ]);
    }
}
