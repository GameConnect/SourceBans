<?php

namespace SourceBans\Form;

use SourceBans\Entity\Appeal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppealForm extends AbstractType
{
    /** @var RequestStack */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $request = $this->requestStack->getMasterRequest();

        $builder
            ->add('banSteam', null, [
                'attr' => [
                    'placeholder' => '[U:1:XXXXXX]',
                ],
                'label' => 'Steam ID',
                'mapped' => false,
            ])
            ->add('banIp', null, [
                'attr' => [
                    'value' => $request->getClientIp(),
                ],
                'label' => 'IP address',
                'mapped' => false,
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Reason',
            ])
            ->add('userEmail', null, [
                'label' => 'Your email address',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appeal::class,
        ]);
    }
}
