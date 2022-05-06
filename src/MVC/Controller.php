<?php
namespace Nish\MVC;

use Nish\Commons\Di;
use Nish\Http\Request;
use Nish\Logger\Logger;
use Nish\Logger\NishLoggerContainer;
use Nish\PrimitiveBeast;
use Nish\Routes\RouteManager;
use Nish\Sessions\SessionManagerContainer;
use Nish\Utils\ITranslator;
use Nish\Utils\Translator;

class Controller extends PrimitiveBeast implements IController
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

    /* @var \Nish\Events\EventManager */
    protected $eventManager;

    /* @var \Symfony\Component\HttpFoundation\Session\Session */
    protected $sessionManager;

    /* @var IModule */
    private $module = null;

    /* @var View */
    protected $view;

    /* @var string */
    protected $viewDir;

    /* @var bool */
    protected $viewIsDisabled = false;

    /* @var ILayout */
    private $layout = null;

    public function __construct()
    {
        $this->router = new RouteManager();

        if (Di::has(self::$TRANSLATOR_CONTAINER_KEY)) {
            $this->translator = Di::get(self::$TRANSLATOR_CONTAINER_KEY);
        }

        if (NishLoggerContainer::exists(self::$LOGGER_CONTAINER_KEY)) {
            $this->logger = NishLoggerContainer::get(self::$LOGGER_CONTAINER_KEY);
        }

        $this->request = Request::getFromGlobals();

        if (SessionManagerContainer::exists(self::$SESSION_MANAGER_CONTAINER_KEY)) {
            $this->sessionManager = SessionManagerContainer::get(self::$SESSION_MANAGER_CONTAINER_KEY);
        }

        if (Di::has(self::$EVENT_MANAGER_KEY)) {
            $this->eventManager = Di::get(self::$EVENT_MANAGER_KEY);
        }

        $this->view = new View();
    }

    /**
     * @return View
     *
     * @override
     */
    public function getView(): View
    {
        return $this->view;
    }

    /**
     * @override
     *
     * @param false $returnResult
     * @param null $viewFile
     * @param null $viewDir
     * @return false|string
     *
     */
    public function renderView($returnResult = false, $viewFile = null, $viewDir = null)
    {
        if ($viewDir != null) {
            $this->setViewDir($viewDir);
        }

        if ($viewFile == null) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $callerAction = preg_replace('/Action$/i', '', $backtrace[1]['function']);


            $viewFile = $callerAction . '.phtml';
        }

        $this->view->setRendered(true);

        $output = $this->view->render($this->getViewDir().'/'.$viewFile);

        if ($returnResult) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * @override
     *
     * @return string|null
     */
    public function getViewDir()
    {
        return $this->viewDir;
    }

    /**
     * @override
     *
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
     * @override
     *
     * @return bool
     */
    public function isViewDisabled(): bool
    {
        return $this->viewIsDisabled;
    }

    /**
     * @override
     */
    public function disableView(): void
    {
        $this->viewIsDisabled = true;
    }

    /**
     * @override
     */
    public function enableView(): void
    {
        $this->viewIsDisabled =false;
    }

    /**
     * @override
     *
     * @return IModule | null
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @override
     *
     * @param IModule $module
     */
    public function setModule($module): void
    {
        $this->module = $module;
    }

    /**
     * @override
     *
     * @return ILayout | null
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @override
     *
     * @param ILayout $layout
     */
    public function setLayout(ILayout $layout): void
    {
        $this->layout = $layout;
    }

}