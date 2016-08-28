<?php

namespace SourceBans\CoreBundle\Form\Setting;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * LanguageType
 */
class LanguageType extends AbstractType
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
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
            'choices' => $this->getLanguageChoices([
                'cs',
                'de',
                'en',
                'fr',
                'nl',
                'pl',
                'pt',
                'ru',
            ]),
        ]);
    }

    /**
     * @param array $languages
     * @return array
     */
    private function getLanguageChoices(array $languages)
    {
        $choices = [];
        foreach ($languages as $language) {
            $choices[$this->getLanguageName($language)] = $language;
        }

        return $choices;
    }

    /**
     * @param string $language
     * @return string
     */
    private function getLanguageName($language)
    {
        $name = Intl::getLanguageBundle()->getLanguageName($language, null, $language);
        if ($language != $this->locale) {
            $name .= ' (' . Intl::getLanguageBundle()->getLanguageName($language, null, $this->locale) . ')';
        }

        return $name;
    }
}
