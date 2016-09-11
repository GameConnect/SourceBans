<?php

namespace SourceBans\CoreBundle\Form\Group;

use SourceBans\CoreBundle\Event\LoadRolesEvent;
use SourceBans\CoreBundle\Event\RoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * RoleType
 */
class RoleType extends AbstractType
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

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
        $event = new LoadRolesEvent;
        $this->dispatcher->dispatch(RoleEvents::LOAD, $event);

        $resolver->setDefaults([
            'choice_label' => function ($role) {
                return 'roles.' . strtolower(str_replace('ROLE_', '', $role));
            },
            'choice_translation_domain' => true,
            'choices' => $event->getRoles(),
            'expanded' => true,
            'multiple' => true,
        ]);
    }
}
