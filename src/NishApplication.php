<?php


namespace Nish;


use Nish\Annotations\IAnnotation;
use Nish\Annotations\OnAfterAction;
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
use Nish\Pipes\Pipe;
use Nish\Routes\RouteManager;
use Nish\Sessions\SessionManagerContainer;
use Nish\Utils\CallableHelper;
use Nish\Utils\DateTime\NishDateTime;
use ReflectionAttribute;

class NishApplication extends PrimitiveBeast
{
    use ModuleTrait;

    /* @var IModule */
    private $module = null;

    /* @var Pipe */
    private $beforeActionPipe;

    /* @var Pipe */
    private $afterActionPipe;

    public function __construct()
    {

    }

    private function retrieveAnnotationsForPhp8(IController $controller, string $actionMethod)
    {
        $classReflector = new \ReflectionClass($controller);
        $methodReflector = new \ReflectionMethod($controller, $actionMethod);

        $classAttributes = $classReflector->getAttributes(IAnnotation::class, ReflectionAttribute::IS_INSTANCEOF);
        $methodAttributes = $methodReflector->getAttributes(IAnnotation::class, ReflectionAttribute::IS_INSTANCEOF);

        $this->afterActionPipe->push([$controller, 'onJustAfterAllActions'], true);

        /* @var $attr */
        foreach ($classAttributes as $attr) {
            $obj = $attr->newInstance();

            if ($obj instanceof OnAfterAction) {
                foreach ($attr->getArguments() as $callableArg) {
                    if (is_array($callableArg) && is_string($callableArg[0]) && is_array($callableArg[2])) {
                        $classObj = new $callableArg[0]($callableArg[2]);

                        $callableArg = [$classObj, $callableArg[1]];
                    }
                    $this->afterActionPipe->push($callableArg, true );
                }
            } else {
                $this->beforeActionPipe->push([$obj, 'run'], true);
            }
        }

        /* @var $attr */
        foreach ($methodAttributes as $attr) {
            $obj = $attr->newInstance();

            if ($obj instanceof OnAfterAction) {
                foreach ($attr->getArguments() as $callableArg) {
                    if (is_array($callableArg) && count($callableArg) >= 3 && is_string($callableArg[0]) && is_array($callableArg[2])) {
                        $classObj = new $callableArg[0]($callableArg[2]);

                        $callableArg = [$classObj, $callableArg[1]];
                    }

                    $this->afterActionPipe->push($callableArg, true );
                }
            } else {
                $this->beforeActionPipe->push([$obj, 'run'], true);
            }
        }

        $this->beforeActionPipe->push([$controller, 'onJustBeforeAllActions'], true);
    }

    private function retrieveAnnotationsForPhp7(IController $controller, string $actionMethod)
    {
        $classReflector = new \ReflectionClass($controller);
        $methodReflector = new \ReflectionMethod($controller, $actionMethod);

        $parser = new \Doctrine\Common\Annotations\DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        $classObjects = $parser->parse($classReflector->getDocComment());
        $methodObjects = $parser->parse($methodReflector->getDocComment());

        $this->afterActionPipe->push([$controller, 'onJustAfterAllActions'], true);

        foreach ($classObjects as $obj) {
            if ($obj instanceof OnAfterAction) {
                foreach ($obj->getParams() as $callableArg) {
                    if (is_array($callableArg) && is_string($callableArg[0]) && is_array($callableArg[2])) {
                        $classObj = new $callableArg[0]($callableArg[2]);

                        $callableArg = [$classObj, $callableArg[1]];
                    }

                    $this->afterActionPipe->push($callableArg, true );
                }
            } else {
                $this->beforeActionPipe->push([$obj, 'run'], true);
            }
        }

        foreach ($methodObjects as $obj) {
            if ($obj instanceof OnAfterAction) {
                foreach ($obj->getParams() as $callableArg) {
                    if (is_array($callableArg) && count($callableArg) >= 3 && is_string($callableArg[0]) && is_array($callableArg[2])) {
                        $classObj = new $callableArg[0]($callableArg[2]);

                        $callableArg = [$classObj, $callableArg[1]];
                    }

                    $this->afterActionPipe->push($callableArg, true );
                }
            } else {
                $this->beforeActionPipe->push([$obj, 'run'], true);
            }
        }

        $this->beforeActionPipe->push([$controller, 'onJustBeforeAllActions'], true);
    }

    private function retrieveAnnotations(IController $controller, string $actionMethod)
    {
        $this->beforeActionPipe = new Pipe();
        $this->afterActionPipe = new Pipe();

        if (self::isPHP8()) {
            $this->retrieveAnnotationsForPhp8($controller, $actionMethod);
        } else {
            $this->retrieveAnnotationsForPhp7($controller, $actionMethod);
        }
    }

    /**
     * @param IController $controller
     * @param string $actionMethod
     * @param array|null $params
     * @return false|string|null
     */
    private function runAction($controller, string $actionMethod, ?array $params = null)
    {

        $controller->setModule($this->module);

        if ($this->module != null && $this->module->areViewsDisabled()) {
            $controller->disableView();
        }

        ob_start();

        $this->retrieveAnnotations($controller, $actionMethod);

        $beforeAllResult = $this->beforeActionPipe->flush($params);

        if (isset($beforeAllResult)) {
            $params[] = $beforeAllResult;
        }

        $actionCallResult = call_user_func_array([$controller, $actionMethod], $params);

        $afterAllCallbackParams = [];
        if (isset($actionCallResult)) {
            $afterAllCallbackParams[] = $actionCallResult;
        }

        $this->afterActionPipe->flush($afterAllCallbackParams);

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

                if (empty($viewDir)) {
                    $viewDir = $this->getViewDir().'/';
                }


                $callerController = preg_replace('/Controller$/i', '', array_reverse(explode('\\',get_class($controller)))[0]);

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
                } else {
                    return null;
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
    public function configure()
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
        if (!GlobalSettings::has(GlobalSettings::SETTING_RESOURCE_NOT_FOUND_ACTION)) {

            self::setResourceNotFoundAction(function () {
                Response::sendResponse('<h1>404 - Not Found</h1>', Response::HTTP_NOT_FOUND);
            });
        }

        // configure default unexpected exception behaviour
        if (!GlobalSettings::has(GlobalSettings::SETTING_UNEXPECTED_BEHAVIOUR_ACTION)) {
            self::setUnexpectedBehaviourAction(function (\Exception $e) {
                $logger = self::getDefaultLogger();

                if ($logger) {
                    $logger->error('NishException: '.$e->getMessage().', Trace: '.$e->getTraceAsString());
                }

                Response::sendResponse('<h1>500 - Interval Server Error</h1>', Response::HTTP_INTERNAL_SERVER_ERROR);
            });

            $this->configured = true;
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

    /**
     * @param $controllerNameOrObject
     * @param $actionName
     * @param array|null $params
     * @param null $moduleNameOrObject
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public function runControllerAction($controllerNameOrObject, $actionName, ?array $params = null, $moduleNameOrObject = null)
    {
        try {
            if (empty($controllerNameOrObject) || empty($actionName)) {
                throw new ResourceNotFoundException('Action or controller not found!');
            }

            if (!$this->isConfigured()) {
                $this->configure();
            }

            if (is_string($controllerNameOrObject)) {
                $controllerNameOrObject = new $controllerNameOrObject();
            }

            if (!($controllerNameOrObject instanceof IController)) {
                throw new InvalidTypeException('Controller is required to be type of IController!');
            }

            if (!empty($moduleNameOrObject)) {

                if (is_string($moduleNameOrObject)) {
                    $this->module = new $moduleNameOrObject();
                } else {
                    $this->module = $moduleNameOrObject;
                }

                if (!$this->module->isConfigured()) {
                    $this->module->configure();
                }

                if ($this->areViewsDisabled()) {
                    $this->module->disableViews();
                }

                if ($this->module->getLayout() == null) {
                    $this->module->setLayout($this->getLayout());
                }
            }

            if ($params === null) $params = [];

            (Request::getFromGlobals())->setUrlArgs($params);

            $response = $this->runAction($controllerNameOrObject, $actionName, $params);

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

    /**
     * Handles requests
     */
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

            $pathInfo = preg_replace('/^'.str_replace("/","\\/", $routeManager->getBasePath()).'/', '', $request->getPathInfo());

            $actionParams = [];

            try {
                $matchedAttributes = $routeManager->matchPath($pathInfo);
            } catch (\Exception $e) {}

            $module = null;

            // call closure or throw 404 status
            if( is_array($matchedAttributes) && !empty($matchedAttributes['_route'])) {
                $route = $routeManager->getRouteByName($matchedAttributes['_route']);

                $middlewareReturn = null;

                if ($route->getMiddleware() != null) {
                    $middlewareReturn = CallableHelper::callUserFunc($route->getMiddleware());
                }

                $module = $route->getModuleClassNameOrObj();
                $controller = $route->getAction()[0];
                $action = $route->getAction()[1];

                $actionParams[0] = $matchedAttributes;

                unset($actionParams[0]['_route']);

                if ($middlewareReturn !== null) {
                    $actionParams[1] = $middlewareReturn;
                }
            }
            /** END: Match Route **/

            if (empty($controller) || empty($action)) {
                throw new ResourceNotFoundException('Action or controller not found!');
            }


            $this->runControllerAction($controller, $action, $actionParams, $module);

        } catch (ResourceNotFoundException $e) {
            self::callResourceNotFoundAction($e);
        } catch (\Exception $e) {
            self::callUnexpectedBehaviourAction($e);
        }

    }
}