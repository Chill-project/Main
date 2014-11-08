<?php

namespace Chill\MainBundle\Form\Type;

/*
 * TODO
 */

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TranslatableStringFormType extends AbstractType
{
    private $availableLanguages;  // The langauges availaible
    private $frameworkTranslatorFallback; // The langagues used for the translation

    public function __construct(array $availableLanguages, $frameworkTranslatorFallback) {
        $this->availableLanguages = $availableLanguages;
        $this->frameworkTranslatorFallback = $frameworkTranslatorFallback;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        foreach ($this->availableLanguages as $lang) {
            $builder->add($lang, 'text',
                array('required' => ($lang === $this->frameworkTranslatorFallback)));
        }
    }

    public function getName()
    {
        return 'translatable_string';
    }
}