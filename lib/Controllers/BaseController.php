<?php

namespace CLMVC\Controllers;

use CLMVC\Core\Container;
use CLMVC\Core\Debug;
use CLMVC\Events\Filter;
use CLMVC\Helpers\Communication;
use CLMVC\Events\Hook;
use CLMVC\Interfaces\IFilter;
use ReflectionMethod;
use CLMVC\Controllers\Render\Rendering;
use Symfony\Component\Config\Definition\Exception\Exception;

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
    private $renderer;

    private $templateType = 'php';
    private $headers = array();
    private $code = 200;

    /**
     * @var [string][IFilter]
     */
    private $filters;
    private $params;
    private $actionRan;
    private $prevent_headers=false;

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
            $request_body = $request_body? $request_body:'{}';
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
     */
    public function __construct()
    {
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
            $perform = true;
            $action_params = $reflection->getParameters();
            $paramValues = $this->getParameters($getParams, $action_params);
            $this->performFilter('beforeAction', $action, $perform);

            if ($perform) {
                Hook::run($this->controller.'-pre'.ucfirst($action), $this);
                call_user_func_array(array($this, $action), $paramValues);
                $this->actionRan = true;
                Hook::run($this->controller.'-post'.ucfirst($action), $this);
            }

            if ($this->actionHasRun()) {
                $this->performFilter('afterActionHasRun', $action);
            }

            $this->performFilter('afterAction', $action);

            if ($this->renderer->canRender()) {
                $this->renderer->RenderToAction($action);
            }
            $this->setupHeadersAndResponseCode();
        } elseif (method_exists($this, 'notFound')) {
            $this->notFound();
        } else {
            throw new Exception('There are no action that corresponds to request.');
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
    public function getBag() {
        return $this->bag;
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
     * @return boolean
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
            /**
             * @var string $redirectTo
             */
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

    /**
     * @param $getParams
     * @param \ReflectionParameter[] $params
     * @return array
     */
    private function getParameters($getParams, $params)
    {
        $paramValues = array();
        if (!empty($params)) {
            foreach ($params as $param) {
                if (isset($getParams[$param->name])) {
                    $paramValues[] = $getParams[$param->name];
                    $this->params[$param->name] = $getParams[$param->name];
                }
            }
        }
        return $paramValues;
    }

    /**
     * @param string $filter_name
     * @param string $action
     * @param boolean $result
     * @return mixed
     */
    private function performFilter($filter_name, $action, &$result = null)
    {
        if (isset($this->filters[$filter_name])) {
            /**
             * @var IFilter $filter
             */
            foreach ($this->filters[$filter_name] as $filter) {
                $result = $filter->perform($this, $this->values, $action);
            }
        }
    }

    /**
     * Call to prevent setting of headers and response code
     * @param bool $prevent
     */
    public function preventHeaders($prevent=true) {
        $this->prevent_headers=$prevent;
    }

    /**
     */
    private function setupHeadersAndResponseCode()
    {
        global $clmvc_http_code, $aoisora_headers;
        if($this->prevent_headers)
            return;
        $clmvc_http_code = $this->code;
        if ($aoisora_headers) {
            $aoisora_headers = array_merge($aoisora_headers, $this->headers);
        } else {
            $aoisora_headers = $this->headers;
        }

        if(headers_sent())
            return;
        http_response_code($this->code);
        header_remove('X-Powered-By');
        header_remove('X-Pingback');
        header_remove('Pragma');
        if ($clmvc_http_code) {
            $description = get_status_header_desc($clmvc_http_code);
            $protocol = 'HTTP/1.0';
            header("$protocol $clmvc_http_code $description");
        }

        if (!empty($aoisora_headers)) {
            foreach ($aoisora_headers as $header) {
                header($header);
            }
        }
    }
}
