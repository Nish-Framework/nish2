<?php
namespace Nish\Utils;

use Nish\PrimitiveBeast;

class Translator extends PrimitiveBeast implements ITranslator
{
    protected $defaultLocale;
    protected $translationLocale;
    protected $namespace;

    protected $messages = [];

    protected $cacheFile = null;
    protected $cacheDir = null;

    protected $notFoundCallback = null;

    public function __construct(string $locale, $cacheDir = null, $defaultLocale = 'en', $namespace = 'website', ?callable $notFoundCallback = null)
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
                $this->messages = require_once($this->cacheDir.'/'.$this->cacheFile);
            }
        }
    }

    public function translate(string $key, $defaultTranslation = '')
    {
        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }

        if (empty($defaultTranslation)) {
            $defaultTranslation = $key;
        }

        if (is_callable($this->notFoundCallback)) {
            call_user_func_array($this->notFoundCallback, [$this->namespace, $key, $defaultTranslation]);
        }

        return $defaultTranslation;
    }

    public function addResource(array $resource)
    {
        $this->messages = array_merge($this->messages, $resource);

        if ($this->cacheDir) {
            file_put_contents($this->cacheDir.'/'.$this->cacheFile, "<?php \n return ".var_export($this->messages, true).';');
        }
    }

    public function isEmpty()
    {
        return empty($this->messages);
    }


}