<?php

namespace SourceBans\CoreBundle\Form;

use SourceBans\CoreBundle\Entity\Appeal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AppealForm
 */
class AppealForm extends AbstractType
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('banSteam', TextType::class, [
                'attr' => [
                    'value' => 'STEAM_',
                ],
                'label' => 'Steam ID',
                'mapped' => false,
            ])
            ->add('banIp', TextType::class, [
                'attr' => [
                    'value' => $this->requestStack->getCurrentRequest()->getClientIp(),
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

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appeal::class,
        ]);
    }
}
