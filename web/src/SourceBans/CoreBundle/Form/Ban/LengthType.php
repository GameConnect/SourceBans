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
                Ban::LENGTH_PERMANENT => 'Permanent',
                'minutes' => [
                  1  => $this->transChoice('{n} minute|{n} minutes', 1),
                  5  => $this->transChoice('{n} minute|{n} minutes', 5),
                  10 => $this->transChoice('{n} minute|{n} minutes', 10),
                  15 => $this->transChoice('{n} minute|{n} minutes', 15),
                  30 => $this->transChoice('{n} minute|{n} minutes', 30),
                  45 => $this->transChoice('{n} minute|{n} minutes', 45),
                ],
                'hours' => [
                  60  => $this->transChoice('{n} hour|{n} hours', 1),
                  120 => $this->transChoice('{n} hour|{n} hours', 2),
                  180 => $this->transChoice('{n} hour|{n} hours', 3),
                  240 => $this->transChoice('{n} hour|{n} hours', 4),
                  480 => $this->transChoice('{n} hour|{n} hours', 8),
                  720 => $this->transChoice('{n} hour|{n} hours', 12),
                ],
                'days' => [
                  1440 => $this->transChoice('{n} day|{n} days', 1),
                  2880 => $this->transChoice('{n} day|{n} days', 2),
                  4320 => $this->transChoice('{n} day|{n} days', 3),
                  5760 => $this->transChoice('{n} day|{n} days', 4),
                  7200 => $this->transChoice('{n} day|{n} days', 5),
                  8640 => $this->transChoice('{n} day|{n} days', 6),
                ],
                'weeks' => [
                  10080 => $this->transChoice('{n} week|{n} weeks', 1),
                  20160 => $this->transChoice('{n} week|{n} weeks', 2),
                  30240 => $this->transChoice('{n} week|{n} weeks', 3),
                ],
                'months' => [
                  43200  => $this->transChoice('{n} month|{n} months', 1),
                  86400  => $this->transChoice('{n} month|{n} months', 2),
                  129600 => $this->transChoice('{n} month|{n} months', 3),
                  259200 => $this->transChoice('{n} month|{n} months', 6),
                  518400 => $this->transChoice('{n} month|{n} months', 12),
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
