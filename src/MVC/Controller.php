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

    /* @var IView */
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

        $this->translator = Di::getIfExists(self::$TRANSLATOR_CONTAINER_KEY);

        $this->logger = NishLoggerContainer::getIfExists(self::$LOGGER_CONTAINER_KEY);

        $this->request = Request::getFromGlobals();

        $this->sessionManager = SessionManagerContainer::getIfExists(self::$SESSION_MANAGER_CONTAINER_KEY);

        $this->eventManager = Di::getIfExists(self::$EVENT_MANAGER_KEY);

        $this->view = new View();
    }

    /**
     * @return IView
     *
     * @override
     */
    public function getView(): IView
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

        /*if ($viewFile == null) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $callerController = preg_replace('/Controller$/i', '', array_reverse(explode('\\',$backtrace[1]['class']))[0]);
            $callerAction = preg_replace('/Action$/i', '', $backtrace[1]['function']);

            $modulePart = '';
            $module = $this->getModule();

            if ($module != null) {
                if ($module->getViewDir() == null) {
                    $modulePart = array_reverse(explode('\\', get_class($module)))[0].'/';
                }
            }

            $viewFile = $modulePart . $callerController . '/' .$callerAction . '.phtml';
        }*/

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
            $this->viewDir = rtrim(str_replace("\\", '/', $viewDir), '/');
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

    /**
     * @override
     */
    public function onJustBeforeAllActions(...$args) {}

    /**
     * @override
     */
    public function onJustAfterAllActions(...$args) {}
}