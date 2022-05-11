<?php


namespace Nish\Translators;


interface ITranslator
{
    public function translate(string $key, $defaultTranslation = '');
    public function loadTranslations();
    public function addTranslations(array $translationList);
    public function isEmpty();
}