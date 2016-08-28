<?php

namespace SourceBans\CoreBundle\Form\Ban;

use SourceBans\CoreBundle\Entity\Ban;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * LengthType
 */
class LengthType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
        $resolver->setDefaults([
            'choices' => [
                'Permanent' => Ban::LENGTH_PERMANENT,
                'minutes' => [
                  $this->transChoice('{n} minute|{n} minutes', 1)  => 1,
                  $this->transChoice('{n} minute|{n} minutes', 5)  => 5,
                  $this->transChoice('{n} minute|{n} minutes', 10) => 10,
                  $this->transChoice('{n} minute|{n} minutes', 15) => 15,
                  $this->transChoice('{n} minute|{n} minutes', 30) => 30,
                  $this->transChoice('{n} minute|{n} minutes', 45) => 45,
                ],
                'hours' => [
                  $this->transChoice('{n} hour|{n} hours', 1)  => 60,
                  $this->transChoice('{n} hour|{n} hours', 2)  => 120,
                  $this->transChoice('{n} hour|{n} hours', 3)  => 180,
                  $this->transChoice('{n} hour|{n} hours', 4)  => 240,
                  $this->transChoice('{n} hour|{n} hours', 8)  => 480,
                  $this->transChoice('{n} hour|{n} hours', 12) => 720,
                ],
                'days' => [
                  $this->transChoice('{n} day|{n} days', 1) => 1440,
                  $this->transChoice('{n} day|{n} days', 2) => 2880,
                  $this->transChoice('{n} day|{n} days', 3) => 4320,
                  $this->transChoice('{n} day|{n} days', 4) => 5760,
                  $this->transChoice('{n} day|{n} days', 5) => 7200,
                  $this->transChoice('{n} day|{n} days', 6) => 8640,
                ],
                'weeks' => [
                  $this->transChoice('{n} week|{n} weeks', 1) => 10080,
                  $this->transChoice('{n} week|{n} weeks', 2) => 20160,
                  $this->transChoice('{n} week|{n} weeks', 3) => 30240,
                ],
                'months' => [
                  $this->transChoice('{n} month|{n} months', 1)  => 43200,
                  $this->transChoice('{n} month|{n} months', 2)  => 86400,
                  $this->transChoice('{n} month|{n} months', 3)  => 129600,
                  $this->transChoice('{n} month|{n} months', 6)  => 259200,
                  $this->transChoice('{n} month|{n} months', 12) => 518400,
                ],
            ],
        ]);
    }

    /**
     * @param string  $id
     * @param integer $number
     * @param array   $parameters
     * @return string
     */
    protected function transChoice($id, $number, array $parameters = [])
    {
        $parameters['{n}'] = $number;

        return $this->translator->transChoice($id, $number, $parameters);
    }
}
