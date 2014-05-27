<?php
namespace CLMVC\Events;

use CLMVC\Core\Data\ActiveRecordBase;
use CLMVC\Helpers\Communication;
use CLMVC\Helpers\Http;
use CLMVC\Helpers\ObjectUtility;
use CLMVC\Helpers\Resize_Image;
use CLMVC\Core\Data\Repo;

class RequestEvent {
    public $thumbnails;
    /**
     * @var int
     */
    private $width;
    /**
     * @var int
     */
    private $height;
    /**
     * @var string
     */
    private $uploadSubFolder;

    /**
     * @var array
     */
    private $postRequest;
    public function __construct($request = null) {
        if (!is_null($request))
            $this->postRequest = $_REQUEST;
        else
            $this->postRequest = $request;
    }

    /**
     * Loads a CRUD item from a POST request
     * @param ActiveRecordBase $crudItem
     * @param bool $stripPrefix
     * @return bool
     */
    public function loadFromPost($crudItem = null, $stripPrefix = false) {
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

        $values = $this->getPostRequest();
        if ($stripPrefix){
            $temp=array_search_key($stripPrefix,$values);
            $values=array();
            foreach($temp as $key => $value)
                $values[$this->stripPrefix($stripPrefix,$key)]=$value;
        }
        $values = array_map('stripslashes', $values);
        $lists = array_search_key('_list', $this->getPostRequest());
        $uploads = Communication::getUpload($properties);
        foreach ($uploads as $property => $upload) {
            if (strlen($upload["name"]) > 0) {
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
                    $image->new_height = $info[1] > $height ? $height: $info[1];
                    $image->new_width = $info[0] > $width ? $width: $info[0];
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
            } else if (is_null($this->getPostRequest($property . '_hasimage')) && empty($values[$property])) {
                $values[$property] = '';
            } else if (strpos($this->getPostRequest($property . '_hasimage'), 'ttp') == 1) {
                $url = $this->getPostRequest($property . '_hasimage');
                $name = str_replace(' ', '-', urldecode(basename($url)));
                $name = str_replace('+', '-', $name);
                if (isset($this->thumbnails[$property]) && $this->thumbnails[$property] == 'thumb')
                    $path = UPLOADS_DIR . $folder . 'thumbs/' . $name;
                else
                    $path = UPLOADS_DIR . $folder . $name;
                $values[$property] = $name;

                Http::save_image($url, $path);
                if (isset($this->thumbnails[$property]) && $this->thumbnails[$property][0] == 'create') {
                    $info = getimagesize($path);
                    $image = new Resize_Image;
                    $image->new_height = $info[1] > $height ? $height: $info[1];
                    $image->new_width = $info[0] > $width ? $width: $info[0];
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
            } else if (isset($this->thumbnails[$property]) && $this->thumbnails[$property][0] == 'create') {
                $url = $this->getPostRequest($property . '_hasimage');
                $name = str_replace(' ', '-', urldecode(basename($url)));
                $name = str_replace('+', '-', $name);
                $path = UPLOADS_DIR . $folder . $name;
                $info = getimagesize($path);
                $image = new Resize_Image;
                $image->new_height = $info[1] > $height ? $height: $info[1];
                $image->new_width = $info[0] > $width ? $width: $info[0];
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
        ObjectUtility::setProperties($crudItem, $values);
        foreach ($lists as $method => $value) {
            $settings = ObjectUtility::getCommentDecoration($crudItem, str_ireplace("_list", "", $method) . 'List');
            $db_relation = array_key_exists_v('dbrelation', $settings);
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
                $value = is_array($value) ? $value: array($value);
                foreach ($value as $val) {
                    $object = Repo::getById($db_relation, $val);
                    $objects[] = $object;
                }

            }

            ObjectUtility::addToArray($crudItem, str_ireplace("_list", "", $method), $objects);
        }
        return $crudItem;
    }

    /**
     * @param int $height
     */
    public function setHeight($height) {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @param array $postRequest
     */
    public function setPostRequest($postRequest) {
        $this->postRequest = $postRequest;
    }

    /**
     * @param null|string $key
     * @return array
     */
    public function getPostRequest($key = null) {
        if ($key)
            return isset($this->postRequest[$key]) ? $this->postRequest[$key] : null;
        return $this->postRequest;
    }

    /**
     * @param int $width
     */
    public function setWidth($width) {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @param string $uploadSubFolder
     */
    public function setUploadSubFolder($uploadSubFolder) {
        $this->uploadSubFolder = $uploadSubFolder;
    }

    /**
     * @return string
     */
    public function getUploadSubFolder() {
        return $this->uploadSubFolder;
    }

    /**
     * @param $prefix
     * @param $key
     * @return mixed
     */
    private function stripPrefix($prefix, $key) {
        return str_replace($prefix, '', $key);
    }
}