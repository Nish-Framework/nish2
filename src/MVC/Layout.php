<?php
namespace Nish\MVC;

use Nish\Commons\Di;
use Nish\Events\IEventManager;
use Nish\Http\Request;
use Nish\Logger\Logger;
use Nish\Logger\NishLoggerContainer;
use Nish\PrimitiveBeast;
use Nish\Routes\RouteManager;
use Nish\Sessions\SessionManagerContainer;
use Nish\Translators\ITranslator;
use Nish\Translators\Translator;

abstract class Layout extends PrimitiveBeast implements ILayout
{
    public static $TRANSLATOR_CONTAINER_KEY = Translator::DEFAULT_TRANSLATOR_CONTAINER_KEY;
    public static $SESSION_MANAGER_CONTAINER_KEY = SessionManagerContainer::DEFAULT_MANAGER_CONTAINER_KEY;
    public static $LOGGER_CONTAINER_KEY = NishLoggerContainer::DEFAULT_LOGGER_CONTAINER_KEY;
    public static $EVENT_MANAGER_KEY = self::DEFAULT_EVENT_MANAGER_CONTAINER_KEY;

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

    /* @var IEventManager */
    protected $eventManager;

    /* @var IModule */
    private $module = null;

    /* @var IView */
    protected $view;

    /* @var string */
    protected $viewFile;

    /* @var string */
    private $controllerOutput = '';

    public function __construct()
    {
        $this->router = new RouteManager();

        $this->translator = Di::getIfExists(self::$TRANSLATOR_CONTAINER_KEY);

        $this->logger = NishLoggerContainer::getIfExists(self::$LOGGER_CONTAINER_KEY);

        $this->request = Request::getFromGlobals();

        $this->sessionManager = SessionManagerContainer::getIfExists(self::$SESSION_MANAGER_CONTAINER_KEY);

        $this->eventManager = Di::getIfExists(self::$EVENT_MANAGER_KEY);

        $this->view = new View();
    }

    /**
     * @return IView
     */
    public function getView()
    {
        return $this->view;
    }


    /**
     * @return string
     */
    public function getViewFile()
    {
        return $this->viewFile;
    }

    /**
     * @param string $viewFile
     */
    public function setViewFile(string $viewFile): void
    {
        $this->viewFile = $viewFile;
    }

    /**
     * @return string
     */
    public function getControllerOutput()
    {
        return $this->controllerOutput;
    }

    /**
     * @param string $controllerOutput
     */
    public function setControllerOutput(string $controllerOutput): void
    {
        $this->controllerOutput = $controllerOutput;
    }

    /**
     * @return IModule | null
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param IModule $module
     */
    public function setModule($module): void
    {
        $this->module = $module;
    }
}