<?php
namespace Nish\Translators;

use Nish\PrimitiveBeast;
use Nish\Utils\CallableHelper;

class Translator extends PrimitiveBeast implements ITranslator
{
    protected $defaultLocale;
    protected $translationLocale;
    protected $namespace;

    protected $translations = [];

    protected $cacheFile = null;
    protected $cacheDir = null;

    protected $notFoundCallback = null;

    public function __construct(string $locale, $cacheDir = null, $defaultLocale = 'en', $namespace = 'default', ?callable $notFoundCallback = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->translationLocale = $locale;
        $this->namespace = $namespace;
        $this->notFoundCallback = $notFoundCallback;

        $this->cacheDir = $cacheDir;

        $this->loadTranslations();
    }

    public function loadTranslations()
    {
        if ($this->cacheDir) {
            $this->cacheFile = $this->namespace.'_'.$this->translationLocale.'.php';

            if (is_file($this->cacheDir.'/'.$this->cacheFile)) {
                $this->translations = require_once($this->cacheDir.'/'.$this->cacheFile);
            }
        }
    }

    public function translate(string $key, $defaultTranslation = '')
    {
        if (isset($this->translations[$key])) {
            return $this->translations[$key];
        }

        if (empty($defaultTranslation)) {
            $defaultTranslation = $key;
        }

        if (CallableHelper::isCallable($this->notFoundCallback)) {
            CallableHelper::callUserFuncArray($this->notFoundCallback, [$this->namespace, $key, $defaultTranslation]);
        }

        return $defaultTranslation;
    }

    public function addTranslations(array $translationList)
    {
        $this->translations = array_merge($this->translations, $translationList);

        if ($this->cacheDir) {
            file_put_contents($this->cacheDir.'/'.$this->cacheFile, "<?php \n return ".var_export($this->translations, true).';');
        }
    }

    public function isEmpty()
    {
        return empty($this->translations);
    }


}