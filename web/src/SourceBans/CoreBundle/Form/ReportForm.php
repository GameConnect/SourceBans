<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Entity\Report;
use SourceBans\CoreBundle\Form\Server\IdType as ServerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ReportForm
 */
class ReportForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('steam', null, [
                'label' => 'Steam ID',
            ])
            ->add('ip', null, [
                'label' => 'IP address',
            ])
            ->add('name', null, [
                'label' => 'Name',
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Reason',
            ])
            ->add('userName', null, [
                'label' => 'Your name',
            ])
            ->add('userEmail', EmailType::class, [
                'label' => 'Your email address',
            ])
            ->add('server', ServerType::class, [
                'label' => 'Server',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}
