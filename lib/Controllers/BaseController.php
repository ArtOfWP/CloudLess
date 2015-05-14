<?php

namespace CLMVC\Controllers;

use CLMVC\Core\Container;
use CLMVC\Core\Debug;
use CLMVC\Events\Filter;
use CLMVC\Events\RequestEvent;
use CLMVC\Helpers\Communication;
use CLMVC\Events\Hook;
use ReflectionMethod;
use CLMVC\Controllers\Render\Rendering;

/**
 * Class BaseController
 * The base class to use for Controllers.
 *
 * @method onControllerPreInit
 * @method onControllerInit
 * @method notFound
 */
class BaseController
{
    /**
     * @var string currently controller
     */
    protected $controller;
    /**
     * @var string action detected
     */
    protected $action;
    /**
     * @var string the default action to run if is empty
     */
    protected $defaultAction = 'index';

    /**
     * @var bool If there should be a redirect
     */
    protected $redirect;
    /**
     * @var string the path to the views for the controller
     */
    protected $viewpath;
    /**
     * @var bool If Controller should render or not
     */
    private $render = true;
    /**
     * @var BaggedValues The values loaded by the action to be used by the view.
     */
    protected $bag;
    /**
     * @var array THe values retrieved by the HTTP method
     */
    public $values = array();

    /**
     * @var string The folder to upload files too
     */
    protected $uploadSubFolder;
    /**
     * @var int The width images should resize to.
     */
    protected $width;
    /**
     * @var int The height images should resize to.
     */
    protected $height;
    /**
     * @var string Where thumbnails should be stored
     */
    protected $thumbnails;

    /**
     * @var Rendering
     */
    protected $renderer;

    private $templateType = 'php';
    private $headers = array();
    private $code = 200;

    /**
     * @var [string][IFilter]
     */
    private $filters;
    private $params;
    private $actionRan;

    /**
     * Setup the controller.
     */
    private function initiate()
    {
        Hook::run('controller-init');
        if (method_exists($this, 'onControllerPreInit')) {
            $this->onControllerPreInit();
        }

        $class = get_class($this);
        $pos = strrpos($class, '\\');
        $className = substr($class, $pos + 1);
        $this->controller = str_replace('Controller', '', $className);
        $this->values = Communication::getQueryString();
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') {
            $request_body = file_get_contents('php://input');
            $_POST = array_merge($_POST, json_decode($request_body, true));
        }
        $this->values = array_merge($this->values, Communication::getFormValues());

        if (method_exists($this, 'onControllerInit')) {
            $this->onControllerInit();
        }
    }

    /**
     * Initiate the controller and execute filter.
     */
    public function init()
    {
        $this->initiate();
        if (isset($this->filters['init']) && is_array($this->filters['init'])) {
            foreach ($this->filters['init'] as $filter) {
                $filter->perform($this, $this->values);
            }
        }
    }

    /**
     * Sets initial variables etc.
     *
     * @param string $viewPath THe path to teh views
     */
    public function __construct($viewPath = '')
    {
        $this->viewpath = $viewPath;
        Debug::Message('Loaded '.$this->controller.' extends BaseController');
        $this->renderer = new Rendering($this);
        $this->bag = Container::instance()->fetch('Bag');
    }

    /**
     * Executes an action on the controller.
     *
     * @param string $action    The name of the action to execute.
     * @param array  $getParams values part of routing
     *
     * @throws \RuntimeException Thrown if action is not found.
     */
    public function executeAction($action, $getParams = array())
    {
        if (method_exists($this, $action)) {
            $reflection = new ReflectionMethod($this, $action);
            if (!$reflection->isPublic()) {
                trigger_error(sprintf('The action you tried to execute is not public: %s', $action));
                if (method_exists($this, 'notFound')) {
                    $this->notFound();
                }
            }

            $this->action = $action;
            $params = $reflection->getParameters();
            $paramValues = array();
            $paramStore = array();
            if (!empty($params)) {
                foreach ($params as $param) {
                    if (isset($getParams[$param->getName()])) {
                        $paramValues[] = $getParams[$param->getName()];
                        $paramStore[$param->getName()] = $getParams[$param->getName()];
                    }
                }
            }
            $this->params = $paramStore;
            $perform = true;
            if (isset($this->filters['beforeAction'])) {
                foreach ($this->filters['beforeAction'] as $filter) {
                    $perform = $filter->perform($this, $this->values, $action);
                }
            }
            if ($perform) {
                Hook::run($this->controller.'-pre'.ucfirst($action), $this);
                call_user_func_array(array($this, $action), $paramValues);
                $this->actionRan = true;
                Hook::run($this->controller.'-post'.ucfirst($action), $this);
            }
            if ($this->actionHasRun() && isset($this->filters['afterActionHasRun'])) {
                foreach ($this->filters['afterActionHasRun'] as $filter) {
                    $filter->perform($this, $this->values, $action);
                }
            }

            if (isset($this->filters['afterAction'])) {
                foreach ($this->filters['afterAction'] as $filter) {
                    $filter->perform($this, $this->values, $action);
                }
            }

            if ($this->renderer->canRender()) {
                $this->renderer->RenderToAction($action);
            }
            http_response_code($this->code);
            global $clmvc_http_code;
            $clmvc_http_code = $this->code;
            global $aoisora_headers;
            if ($aoisora_headers) {
                $aoisora_headers = array_merge($aoisora_headers, $this->headers);
            } else {
                $aoisora_headers = $this->headers;
            }
        } elseif (method_exists($this, 'notFound')) {
            $this->notFound();
        }
    }
    public function setStatusCode($code)
    {
        $this->code = $code;
    }
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    /**
     * Retrieves the bag filled with values set by the action.
     *
     * @return BaggedValues
     */
    public function getBag()
    {
        static $bag;
        if ($bag) {
            return $bag;
        }

        return $bag = Filter::run($this->controller.'-bag', array($this->bag, $this->controller, $this->action, $this->values));
    }

    /**
     * @return string
     */
    public function getTemplateType()
    {
        return $this->templateType;
    }

    /**
     * @param mixed $templateType
     */
    public function setTemplateType($templateType)
    {
        $this->templateType = $templateType;
    }

    /**
     * @return Rendering
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @return bool
     */
    public function getRender()
    {
        return $this->render;
    }

    /**
     * @param bool $render
     */
    public function setRender($render)
    {
        $this->render = $render;
    }

    /**
     * @return mixed
     */
    public function actionHasRun()
    {
        return $this->actionRan;
    }

    /**
     * Redirect request with query.
     *
     * @param string|array $query
     */
    protected function redirect($query = array())
    {
        if (defined('NO_REDIRECT') && NO_REDIRECT) {
            return;
        }
        $redirect = Communication::useRedirect();
        if ($redirect) {
            if (strtolower($redirect) == 'referer') {
                $redirectTo = preg_replace('/[\&|\?]result\=\d+/', '', Communication::getReferer());
            } else {
                $redirectTo = preg_replace('/[\&|\?]result\=\d+/', '', $redirect);
            }
            Communication::redirectTo($redirectTo, $query);
        }
    }

    /**
     * Set the default action to use.
     *
     * @param $action
     */
    protected function setDefaultAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewpath;
    }

    /**
     * @param $when
     * @param $filter
     *
     * @throws \InvalidArgumentException
     */
    public function addFilter($when, $filter)
    {
        if (!method_exists($filter, 'perform')) {
            throw new \InvalidArgumentException('Supplied filter does not implement the required perform method.');
        }
        if (!isset($this->filters[$when])) {
            $this->filters[$when] = [];
        }
        $this->filters[$when][] = $filter;
    }

    /**
     * Retrieves value from the POST/GET etc array.
     *
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public function getValue($key, $default = null)
    {
        return isset($this->values[$key]) ? $this->values[$key] : $default;
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param $param
     *
     * @return mixed|null
     */
    public function getActionParam($param)
    {
        return isset($this->params[$param]) ? $this->params[$param] : null;
    }
}
