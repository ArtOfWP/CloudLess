<?php

namespace CLMVC\Events;

class RequestEvent
{
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

    /**
     * @param array $request
     */
    public function __construct($request = [])
    {
        if (empty($request)) {
            $this->postRequest = $_REQUEST;
        } else {
            $this->postRequest = $request;
        }
    }

    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param array $postRequest
     */
    public function setPostRequest($postRequest)
    {
        $this->postRequest = $postRequest;
    }

    /**
     * @param null|string $key
     *
     * @return array
     */
    public function getPostRequest($key = null)
    {
        if ($key !== null && !empty($key)) {
            return isset($this->postRequest[$key]) ? $this->postRequest[$key] : null;
        }

        return $this->postRequest;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $uploadSubFolder
     */
    public function setUploadSubFolder($uploadSubFolder)
    {
        $this->uploadSubFolder = $uploadSubFolder;
    }

    /**
     * @return string
     */
    public function getUploadSubFolder()
    {
        return $this->uploadSubFolder;
    }
}
