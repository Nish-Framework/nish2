<?php
namespace Nish\MVC;


use Nish\Commons\Di;
use Nish\Http\Request;
use Nish\Logger\Logger;
use Nish\Logger\NishLoggerContainer;
use Nish\PrimitiveBeast;
use Nish\Routes\RouteManager;
use Nish\Sessions\SessionManagerContainer;
use Nish\Translators\ITranslator;
use Nish\Translators\Translator;

class View extends PrimitiveBeast implements IView
{
    public static $TRANSLATOR_CONTAINER_KEY = Translator::DEFAULT_TRANSLATOR_CONTAINER_KEY;
    public static $SESSION_MANAGER_CONTAINER_KEY = SessionManagerContainer::DEFAULT_MANAGER_CONTAINER_KEY;
    public static $LOGGER_CONTAINER_KEY = NishLoggerContainer::DEFAULT_LOGGER_CONTAINER_KEY;

    /* @var Logger */
    protected $logger;

    /* @var RouteManager */
    protected $router;

    /* @var ITranslator */
    protected $translator;

    /* @var Request */
    protected $request;

    /* @var \Symfony\Component\HttpFoundation\Session\Session */
    protected $sessionManager;

    /* @var bool */
    protected $rendered = false;

    protected $viewDir;


    public function __construct()
    {

        $this->router = new RouteManager();

        $this->translator = Di::getIfExists(self::$TRANSLATOR_CONTAINER_KEY);

        $this->logger = NishLoggerContainer::getIfExists(self::$LOGGER_CONTAINER_KEY);

        $this->request = Request::getFromGlobals();

        $this->sessionManager = SessionManagerContainer::getIfExists(self::$SESSION_MANAGER_CONTAINER_KEY);

    }

    /**
     * @override
     * @return mixed
     */
    public function getViewDir()
    {
        return $this->viewDir;
    }

    /**
     * @override
     * @param mixed $viewDir
     */
    public function setViewDir($viewDir): void
    {
        $this->viewDir = $viewDir;
    }


    /**
     * @override
     * @return bool
     */
    public function isRendered(): bool
    {
        return $this->rendered;
    }

    /**
     * @override
     * @param bool $rendered
     */
    public function setRendered(bool $rendered): void
    {
        $this->rendered = $rendered;
    }

    /**
     * @override
     * @param string $file
     * @return false|string
     */
    public function render(string $file)
    {
        ob_start();
        include($file);

        return ob_get_clean();
    }
}