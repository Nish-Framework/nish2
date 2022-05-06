<?php
namespace Nish\MVC;


trait ModuleTrait
{

    /* @var string */
    private $viewDir;

    /* @var bool */
    protected $viewsDisabled = false;

    /* @var ILayout */
    private $layout = null;


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
            $this->viewDir = trim(str_replace("\\", '/', $viewDir), '/');
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

}