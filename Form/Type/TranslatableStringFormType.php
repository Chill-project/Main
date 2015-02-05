<?php

namespace Chill\MainBundle\Form\Type;

/*
 * TODO
 */

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\Translator;

class TranslatableStringFormType extends AbstractType
{
    private $availableLanguages;  // The langauges availaible
    private $frameworkTranslatorFallback; // The langagues used for the translation

    public function __construct(array $availableLanguages, Translator $translator) {
        $this->availableLanguages = $availableLanguages;
        $this->frameworkTranslatorFallback = $translator->getFallbackLocales();
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        foreach ($this->availableLanguages as $lang) {
            $builder->add($lang, 'text',
                array('required' => (in_array($lang, 
                      $this->frameworkTranslatorFallback))));
        }
    }

    public function getName()
    {
        return 'translatable_string';
    }
}