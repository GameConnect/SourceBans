<?php

namespace SourceBans\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionalPasswordType extends AbstractType
{
    private $originalValue;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            if ($event->getData()) {
                $this->originalValue = $event->getData();
                $event->setData($options['placeholder_value']);
            }
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            if ($event->getData() == $options['placeholder_value']) {
                $event->setData($this->originalValue);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'placeholder_value' => 'xxxxx',
            'trim' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'password';
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
