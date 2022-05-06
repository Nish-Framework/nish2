<?php


namespace Nish;


use Nish\Commons\Di;
use Nish\Commons\GlobalSettings;
use Nish\Events\EventManager;
use Nish\Events\IEventManager;
use Nish\Exceptions\InvalidTypeException;
use Nish\Exceptions\ResourceNotFoundException;
use Nish\Http\Request;
use Nish\Http\Response;
use Nish\Logger\Logger;
use Nish\Logger\NishLoggerContainer;
use Nish\MVC\IController;
use Nish\MVC\IModule;
use Nish\MVC\ModuleTrait;
use Nish\Routes\RouteManager;
use Nish\Sessions\SessionManagerContainer;
use Nish\Utils\DateTime\NishDateTime;

class NishApplication extends PrimitiveBeast
{
    use ModuleTrait;

    /* @var IModule */
    private $module = null;

    public function __construct()
    {

    }

    /**
     * @param string $controllerClass
     * @param string $actionMethod
     * @param array|null $params
     * @return false|string
     */
    private function runAction(string $controllerClass, string $actionMethod, ?array $params = null)
    {

        /* @var IController $controller */
        $controller = new $controllerClass();

        $controller->setModule($this->module);

        if ($this->module != null && $this->module->areViewsDisabled()) {
            $controller->disableView();
        }

        ob_start();

        call_user_func_array([$controller, $actionMethod], $params);

        // if view is not disabled and not rendered, render it
        if (!$controller->isViewDisabled() && !$controller->getView()->isRendered()) {

            $viewFile = preg_replace('/Action$/i', '', $actionMethod) . '.phtml';

            if (empty($controller->getViewDir())) {
                $viewDir = '';

                if ($this->module != null) {
                    if (!empty($this->module->getViewDir())) {
                        $viewDir = $this->module->getViewDir().'/';
                    } else {
                        $viewDir = array_reverse(explode('\\', get_class($this->module)))[0].'/';
                    }
                }

                /*if (empty($viewDir)) {
                    $viewDir = $this->getViewDir();
                }*/

                $callerController = preg_replace('/Controller$/i', '', array_reverse(explode('\\',$controllerClass))[0]);

                $viewDir .= $callerController;

                $controller->setViewDir($viewDir);
            }

            $controller->renderView(false, $viewFile);
        }

        $actionOutput = ob_get_clean();

        if (!$controller->isViewDisabled()) {
            $layout = $controller->getLayout();

            if ($layout == null && $this->module != null) {
                $layout = $this->module->getLayout();
            }

            if ($layout != null) {
                $layout->setControllerOutput($actionOutput);
                $layout->setModule($this->module);
                $layout->setLayoutAction();
                $layoutView = $layout->getView();

                if ($layoutView != null) {
                    $layoutView->controllerOutput = $layout->getControllerOutput();
                    return $layoutView->render($layout->getViewFile());
                }
            } else {
                return $actionOutput;
            }
        } else {
            return $actionOutput;
        }
    }//end method runAction;

    /**
     * @throws Exceptions\ContainerObjectNotFoundException
     * @throws InvalidTypeException
     */
    protected function configure()
    {
        // set default log level
        if (!GlobalSettings::has(GlobalSettings::SETTING_APP_LOG_LEVEL)) {
            if (self::isAppInDebugMode()) {
                self::setLogLevel(Logger::DEBUG);
            } else {
                self::setLogLevel(Logger::WARNING);
            }
        }

        // configure default logger
        if (!NishLoggerContainer::exists(NishLoggerContainer::DEFAULT_LOGGER_CONTAINER_KEY)) {
            $streamHandler = new \Monolog\Handler\StreamHandler(__DIR__.'/logs/'.(NishDateTime::format(time(),'Y-m-d')).'.log', self::getLogLevel());

            NishLoggerContainer::configure(
                [$streamHandler]
            );
        }

        //set default response content type
        if (!Response::hasDefaultHeader('Content-Type')) {
            Response::addDefaultHeader('Content-Type', 'text/html');
        }

        //set default response charset
        if (empty(Response::getDefaultCharset())) {
            Response::setDefaultCharset('UTF-8');
        }

        //configure default event manager
        if (!Di::has(self::DEFAULT_EVENT_MANAGER_CONTAINER_KEY)){
            self::setDefaultEventManager(function () {
                return new \Nish\Events\EventManager();
            });
        }

        // configure default not found action
        if (!self::getResourceNotFoundAction()) {
            self::setResourceNotFoundAction(function () {
                Response::sendResponse('<h1>404 - Not Found</h1>', Response::HTTP_NOT_FOUND);
            });
        }

        // configure default unexpected exception behaviour
        if (!self::getUnexpectedBehaviourAction()) {
            self::setUnexpectedBehaviourAction(function (\Exception $e) {
                $logger = self::getDefaultLogger();

                if ($logger) {
                    $logger->error('Exception: '.$e->getMessage().', Trace: '.$e->getTraceAsString());
                }

                Response::sendResponse('<h1>500 - Interval Server Error</h1>', Response::HTTP_INTERNAL_SERVER_ERROR);
            });
        }

        //configure default session manager
        if (!SessionManagerContainer::exists(SessionManagerContainer::DEFAULT_MANAGER_CONTAINER_KEY)) {

            SessionManagerContainer::set(
                function () {
                    $session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(), new \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag());

                    if (!$session->isStarted()) {
                        $session->start();
                    }

                    return $session;
                },

                SessionManagerContainer::DEFAULT_MANAGER_CONTAINER_KEY
            );
        }

    }

    /**
     * @param bool $isDebugModeOn
     * @throws InvalidTypeException
     */
    public function setDebugMode(bool $isDebugModeOn)
    {
        GlobalSettings::put(GlobalSettings::SETTING_DEBUG_MODE, $isDebugModeOn);
    }

    /**
     * @param int $level
     * @throws InvalidTypeException
     */
    public static function setLogLevel(int $level)
    {
        GlobalSettings::put(GlobalSettings::SETTING_APP_LOG_LEVEL, $level);
    }

    /**
     * @param string $rootDir
     * @throws InvalidTypeException
     */
    public static function setAppRootDir(string $rootDir)
    {
        GlobalSettings::put(GlobalSettings::SETTING_APP_ROOT_DIR, $rootDir);
    }

    /**
     * @param callable $callable
     * @throws InvalidTypeException
     */
    public static function setResourceNotFoundAction(callable $callable)
    {
        GlobalSettings::put(GlobalSettings::SETTING_RESOURCE_NOT_FOUND_ACTION, $callable);
    }

    /**
     * @param callable $callable
     * @throws InvalidTypeException
     */
    public static function setUnexpectedBehaviourAction(callable $callable)
    {
        GlobalSettings::put(GlobalSettings::SETTING_UNEXPECTED_BEHAVIOUR_ACTION, $callable);
    }

    public function run()
    {
        try {
            /**
             * @var IModule
             */
            $module = null;
            $controller = null;
            $action = null;
            $params = null;

            /** BEGIN: Match Route **/
            // match current request url
            $routeManager = new RouteManager();
            $request = Request::getFromGlobals();

            $matchedAttributes = $routeManager->matchPath($request->getPathInfo());

            // call closure or throw 404 status
            if( is_array($matchedAttributes) && !empty($matchedAttributes['_route'])) {

                $route = $routeManager->getRouteByName($matchedAttributes['_route']);

                $module = $route->getModule();
                $controller = $route->getAction()[0];
                $action = $route->getAction()[1];

                unset($matchedAttributes['_route']);
            }
            /** END: Match Route **/

            if (!empty($module)) {
                $this->module = new $module();

                if ($this->areViewsDisabled()) {
                    $this->module->disableViews();
                }

                if ($this->module->getLayout() == null) {
                    $this->module->setLayout($this->getLayout());
                }

                $this->module->configure();
            }

            $this->configure();

            if (empty($controller) || empty($action)) {
                throw new ResourceNotFoundException('Action or controller is null');
            }

            $response = $this->runAction($controller, $action, $matchedAttributes);

            $eventManager = self::getDefaultEventManager();

            if ($eventManager instanceof IEventManager) {
                echo $eventManager->trigger(EventManager::ON_BEFORE_SEND_RESPONSE, null, $response);
            } else {
                echo $response;
            }

        } catch (ResourceNotFoundException $e) {
            self::callResourceNotFoundAction($e);
        } catch (\Exception $e) {
            self::callUnexpectedBehaviourAction($e);
        }

    }
}