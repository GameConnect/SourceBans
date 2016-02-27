<?php

namespace SourceBans\CoreBundle\Form\Setting;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DefaultPageType
 */
class DefaultPageType extends AbstractType
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
                'dashboard' => 'controllers.default.dashboard.title',
                'bans'      => 'controllers.default.bans.title',
                'servers'   => 'controllers.default.servers.title',
                'report'    => 'controllers.default.report.title',
                'appeal'    => 'controllers.default.appeal.title',
            ],
        ]);
    }
}
