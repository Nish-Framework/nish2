<?php
namespace Nish\MVC;


trait ModuleTrait
{

    /* @var string */
    protected $viewDir;

    /* @var bool */
    protected $viewsDisabled = false;

    /* @var ILayout */
    protected $layout = null;

    private $configured = false;

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * @param bool $configured
     */
    public function setConfigured(bool $configured): void
    {
        $this->configured = $configured;
    }

    /**
     * @return string|null
     */
    public function getViewDir()
    {
        return $this->viewDir;
    }

    /**
     * @param string|null $viewDir
     */
    public function setViewDir(?string $viewDir = null): void
    {
        if ($viewDir == null) {
            $this->viewDir = null;
        } else {
            $this->viewDir = rtrim(str_replace("\\", '/', $viewDir), '/');
        }
    }

    /**
     * @return bool
     */
    public function areViewsDisabled(): bool
    {
        return $this->viewsDisabled;
    }

    public function disableViews(): void
    {
        $this->viewsDisabled = true;
    }

    public function enableViews(): void
    {
        $this->viewsDisabled = false;
    }

    /**
     * @override
     * @return ILayout | null
     */
    public function getLayout(): ILayout
    {
        return $this->layout;
    }

    /**
     * @param ILayout $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function configure()
    {
        $this->configured = true;
    }

}