<?php
namespace CLMVC\Controllers;
use ActiveRecordBase;
use CLMVC\Core\AoiSoraSettings;
use CLMVC\Core\Debug;
use CLMVC\Events\Filter;
use CLMVC\Events\View;
use CLMVC\Helpers\Http;
use CLMVC\Helpers\ObjectUtility;
use CLMVC\Helpers\Resize_Image;
use CLMVC\Interfaces\IFilter;
use CLMVC\Helpers\Communication;
use CLMVC\Events\Hook;
use ReflectionMethod;
use Repo;
use RuntimeException;

/**
 * Class BaseController
 * The base class to use for Controllers
 * @method onControllerPreInit
 * @method onControllerInit
 */
class BaseController {
    /**
     * @var string Currently running action
     */
    private static $currentAction;
    /**
     * @var string currently controller
     */
    protected $controller;
    /**
     * @var string action detected
     */
    protected $action;
    /**
     * @var string the default action to run if $action is empty
     */
    protected $defaultAction = 'index';
    /**
     * @var IFilter The filter to perform before actions
     */
    protected $filter;
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
    public $render = true;
    /**
     * @var array The values loaded by the action to be used by the view.
     */
    public $bag = array();
    /**
     * @var string The rendered content
     */
    public $viewcontent;
    /**
     * @var array THe values retrieved by the HTTP method
     */
    public $values = array();
    /**
     * @var string The current executing controller
     */
    private static $currentController;
    /**
     * @var ActiveRecordBase The object to use for CRUD actions
     */
    protected $crudItem;
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

    /**
     * Setup the controller.
     */
    private function initiate() {
        Hook::run('controller-init');
        if (method_exists($this, 'onControllerPreInit'))
            $this->onControllerPreInit();

        $class = get_class($this);
        $pos = strrpos($class, '\\');
        $className = substr($class, $pos + 1);
        $this->controller = str_replace('Controller', '', $className);
        $this->values = Communication::getQueryString();
        $this->values = array_merge($this->values, Communication::getFormValues());
        if (method_exists($this, 'onControllerInit'))
            $this->onControllerInit();
    }

    /**
     * Initiate the controller and execute filter and do automatic rendering if enabled.
     */
    public function init() {
        $this->initiate();
        if ($this->filter)
            if (!$this->filter->perform($this, $this->values))
                die("Action could not be performed.");
    }

    /**
     * Sets initial variables etc.
     *
     * @param string $viewPath THe path to teh views
     */
    public function __construct($viewPath = '') {
        $this->viewpath = $viewPath;
        Debug::Message('Loaded ' . $this->controller . ' extends BaseController');
        $this->renderer = new Rendering($this);
    }

    /**
     * Executes action and does automatic rendering.
     */
    protected function automaticRender() {
        Debug::Message('Executing automatic action');
        $action = array_key_exists_v(ACTIONKEY, Communication::getQueryString());
        if (!isset($action) || empty($action))
            if ($this->action)
                $action = $this->action;
            else
                $action = 'index';
        Debug::Message('PreExecuted action: ' . $action);
        try {
            $this->executeAction($action);
        } catch (RuntimeException $ex) {
            $this->viewcontent = 'Could not find action: ' . $action;
            $this->renderer->canRender(false);
        }
        if ($this->renderer->canRender()) {
            $this->renderer->Render($this->controller,$action);
        }
    }

    /**
     * Executes an action on the controller.
     * @param string $action The name of the action to execute.
     * @param array $getParams values part of routing
     * @throws \RuntimeException Thrown if action is not found.
     */
    public function executeAction($action, $getParams = array()) {
        if (method_exists($this, $action)) {
            $reflection = new ReflectionMethod($this, $action);
            if (!$reflection->isPublic())
                throw new RuntimeException("The action you tried to execute is not public.");
            self::$currentAction = $action;
            $params = $reflection->getParameters();
            $paramValues = array();
            if ($params) {
                foreach ($params as $param) {
                    $rClass = $param->getClass();
                    if ($rClass) {
                        $pObj = $rClass->newInstance();
                        /** @var $pObj ActiveRecordBase */
                        $paramValues[] = $this->loadFromPost($pObj,$param->getName().'_');
                    } else if (isset($getParams[$param->getName()])) {
                        $paramValues[] = $getParams[$param->getName()];
                    }
                }
            }
            Hook::run($this->controller . '-pre' . ucfirst($action), $this);
            call_user_func_array(array($this, $action), $paramValues);
            Hook::run($this->controller . '-post' . ucfirst($action), $this);
            if ($this->renderer->canRender()) {
                $this->renderer->RenderToAction($action);
            }
        } else
            throw new RuntimeException("The action you tried to execute does not exist. $action");
    }

    /**
     * Retrieves the bag filled with values set by the action.
     * @return mixed
     */
    public function getBag() {
        return Filter::run($this->controller . '-bag', array($this->bag, $this->controller, $this->action, $this->values));
    }

    /**
     * Default Not Found action. Executed when action etc cannot be found.
     */
    public function NotFound() {
        $view = $this->findView($this->controller, 'NotFound');
        if ($view) {
            $this->setUpRouting($this->controller, 'NotFound');
            $this->renderer->Render($this->controller, 'NotFound');
        } else {
            $this->setUpRouting('default', 'NotFound');
            $this->renderer->Render('default', 'NotFound');
        }
    }

    /**
     * Redirect request with query
     * @param array $query
     */
    protected function redirect($query = array()) {
        if (defined('NO_REDIRECT') && NO_REDIRECT)
            return;
        $redirect = Communication::useRedirect();
        if ($redirect) {
            if (strtolower($redirect) == 'referer')
                $redirect = preg_replace('/[\&|\?]result\=\d+/', '', Communication::getReferer());
            else
                $redirect = preg_replace('/[\&|\?]result\=\d+/', '', $redirect);
            Communication::redirectTo($redirect, $query);
        }
    }

    /**
     * The currently executing action
     * @return string
     */
    public static function CurrentAction() {
        return self::$currentAction;
    }

    /**
     * The current running controller
     * @return mixed
     */
    public static function CurrentController() {
        return self::$currentController;
    }

    /**
     * Set the default action to use.
     * @param $action
     */
    protected function setDefaultAction($action) {
        $this->action = $action;
    }

    /**
     * Set up routing. That is the Controller and Action to use.
     * @param $controller
     * @param $action
     */
    public static function setUpRouting($controller, $action) {
        self::$currentController = $controller;
        self::$currentAction = $action;
    }

    /**
     * Loads a CRUD item from a POST request
     * @param ActiveRecordBase $crudItem
     * @param bool $stripPrefix
     * @return bool
     */
    protected function loadFromPost($crudItem = null, $stripPrefix = false) {
        if (!$crudItem)
            $crudItem = $this->crudItem;
        $folder = '';
        $width = 100;
        $height = 100;
        if ($this->uploadSubFolder)
            $folder = $this->uploadSubFolder . '/';
        if ($this->width)
            $width = $this->width;
        if ($this->height)
            $height = $this->height;
        $properties = ObjectUtility::getPropertiesAndValues($crudItem);

        Debug::Message('LoadFromPost');
        $arrvalues = $this->values;
        Debug::Value('Post', $arrvalues);

        //		Debug::Value('Uploaded',Communication::getUpload($properties));

        if ($stripPrefix){
            $temp=array();
            $values=array_search_key($stripPrefix,$this->values);
            foreach($values as $key => $value)
                $temp[$this->stripPrefix($stripPrefix,$key)]=$value;
            $values=$temp;
        }else{
            $values = Communication::getFormValues($properties);
        }
        $values = array_map('stripslashes', $values);
        Debug::Value('Loaded properties/values for ' . get_class($crudItem), $values);
        $lists = array_search_key('_list', $arrvalues);
        Debug::Value('Loaded listvalues from post', $lists);
        $uploads = Communication::getUpload($properties);
        foreach ($uploads as $property => $upload) {
            Debug::Message('CHECKING UPLOADS');
            if (strlen($upload["name"]) > 0) {
                Debug::Message('FOUND UPLOAD');
                $name = str_replace(' ', '-', $upload["name"]);
                $name = str_replace('+', '-', $name);
                if (isset($this->thumbnails[$property]) && $this->thumbnails[$property] == 'thumb')
                    $path = UPLOADS_DIR . $folder . 'thumbs/' . $name;
                else
                    $path = UPLOADS_DIR . $folder . $name;

                move_uploaded_file($upload["tmp_name"], $path);
                chmod($path, octdec(644));
                $values[$property] = $name;
                if (isset($this->thumbnails[$property]) && $this->thumbnails[$property][0] == 'create') {
                    $info = getimagesize($path);
                    $image = new Resize_Image();
                    if ($info[1] > $height)
                        $image->new_height = $height;
                    else if ($info[0] > $width)
                        $image->new_width = $width;
                    else {
                        $image->new_height = $info[1];
                        $image->new_width = $info[0];
                    }
                    $image->image_to_resize = $path;
                    $image->ratio = true;
                    $info = pathinfo($name);
                    $file_name = basename($name, '.' . $info['extension']);
                    $image->new_image_name = $file_name;
                    $image->save_folder = UPLOADS_DIR . $folder . 'thumbs/';
                    $values[$this->thumbnails[$property][1]] = 'thumbs/' . $name;
                    $process = $image->resize();
                    chmod($process['new_file_path'], octdec(644));
                }
            } else {
                if (!isset($this->values[$property . '_hasimage']) && empty($values[$property])) {
                    $values[$property] = '';
                }
                else {
                    if (strpos($this->values[$property . '_hasimage'], 'ttp') == 1) {
                        Debug::Message('HAS IMAGE LINK ' . $property);
                        $url = $this->values[$property . '_hasimage'];
                        $name = str_replace(' ', '-', urldecode(basename($url)));
                        $name = str_replace('+', '-', $name);
                        if (isset($this->thumbnails[$property]) && $this->thumbnails[$property] == 'thumb')
                            $path = UPLOADS_DIR . $folder . 'thumbs/' . $name;
                        else
                            $path = UPLOADS_DIR . $folder . $name;
                        $values[$property] = $name;

                        Http::save_image($url, $path);
                        if (isset($this->thumbnails[$property]) && $this->thumbnails[$property][0] == 'create') {
                            Debug::Message('CREATE THUMBNAIL');
                            $info = getimagesize($path);
                            $image = new Resize_Image;
                            if ($info[1] > $height)
                                $image->new_height = $height;
                            else if ($info[0] > $width)
                                $image->new_width = $width;
                            else {
                                $image->new_height = $info[1];
                                $image->new_width = $info[0];
                            }
                            $image->image_to_resize = $path; // Full Path to the file
                            $image->ratio = true; // Keep Aspect Ratio?
                            $info = pathinfo($name);
                            $file_name = basename($name, '.' . $info['extension']);
                            $image->new_image_name = $file_name;
                            $image->save_folder = UPLOADS_DIR . $folder . 'thumbs/';
                            $values[$this->thumbnails[$property][1]] = 'thumbs/' . $name;
                            $process = $image->resize();
                            chmod($process['new_file_path'], octdec(644));
                        }
                    } else {
                        Debug::Message('HAS IMAGE ' . $property);
                        Debug::Value('Thumbnails', $this->thumbnails);
                        if (isset($this->thumbnails[$property]) && $this->thumbnails[$property][0] == 'create') {
                            Debug::Message('CREATE THUMBNAIL');
                            $url = $this->values[$property . '_hasimage'];
                            $name = str_replace(' ', '-', urldecode(basename($url)));
                            $name = str_replace('+', '-', $name);
                            $path = UPLOADS_DIR . $folder . $name;
                            $info = getimagesize($path);
                            $image = new Resize_Image;
                            if ($info[1] > $height)
                                $image->new_height = $height;
                            else if ($info[0] > $width)
                                $image->new_width = $width;
                            else {
                                $image->new_height = $info[1];
                                $image->new_width = $info[0];
                            }
                            $image->image_to_resize = $path; // Full Path to the file
                            $image->ratio = true; // Keep Aspect Ratio?
                            // Name of the new image (optional) - If it's not set a new will be added automatically
                            $info = pathinfo($name);
                            $file_name = basename($name, '.' . $info['extension']);
                            $image->new_image_name = $file_name;
                            // Path where the new image should be saved. If it's not set the script will output the image without saving it
                            $image->save_folder = UPLOADS_DIR . $folder . 'thumbs/';
                            $values[$this->thumbnails[$property][1]] = 'thumbs/' . $name;
                            $process = $image->resize();
                            chmod($process['new_file_path'], octdec(644));
                        }
                    }
                }
            }
        }
        ObjectUtility::setProperties($crudItem, $values);
        foreach ($lists as $method => $value) {
            Debug::Value($method, $value);
            $settings = ObjectUtility::getCommentDecoration($this->crudItem, str_ireplace("_list", "", $method) . 'List');
            $db_relation = array_key_exists_v('dbrelation', $settings);
            Debug::Value($method, $db_relation);
            $field = array_key_exists_v('field', $settings);
            $objects = array();
            if ($field == 'text') {
                if (strlen($value) == 0)
                    continue;
                $listValues = explode(',', trim($value, " ,."));
                if (sizeof($listValues) == 0)
                    continue;
                foreach ($listValues as $listValue) {
                    if ($db_relation && $field == 'text') {
                        /**
                         * @var ActiveRecordBase $object
                         */
                        $object = new $db_relation;
                        $object->setName(trim($listValue));
                        $object->save();
                        $objects[] = $object;
                    }
                }
            }
            else if ($db_relation) {
                if (is_array($value))
                    foreach ($value as $val) {
                        $object = Repo::getById($db_relation, $val);
                        $objects[] = $object;
                    }
                else {
                    $object = Repo::getById($db_relation, $value);
                    $objects[] = $object;
                }
            }

            ObjectUtility::addToArray($this->crudItem, str_ireplace("_list", "", $method), $objects);
        }
        $this->crudItem = $crudItem;
        return $crudItem;
    }

    /**
     * @param $prefix
     * @param $key
     * @return mixed
     */
    private function stripPrefix($prefix, $key) {
        return str_replace($prefix, '', $key);
    }

    public function getViewPath() {
        return $this->viewpath;
    }
}