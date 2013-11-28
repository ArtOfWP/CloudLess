<?php

namespace CLMVC\Components\Posts;


use CLMVC\Core\Container;
use CLMVC\Interfaces\IPost;

class Post {
    private $methods = array();
    /**
     * @var IPost $post
     */
    private $post = null;
    public function __construct(IPost $post = null) {
        if (is_null($post))
            $this->post = Container::instance()->make('CLMVC\\Interfaces\\IPost');
        else
            $this->post = $post;
        $methods = get_class_methods(get_class($this->post));
        array_shift($methods);
        foreach ($methods as $method) {
            $this->methods[strtolower(substr($method,3))] = $method;
        }
    }
    /*
    public function getID() {
        return $this->post->getID();
    }
    public function getParentID() {
        return $this->post->getParentID();
    }
    public function getTitle() {
        return $this->post->get
    }
    public function getExcerpt() {}
    public function getContent() {}
    public function getPublicationDate() {}
    public function getModificationDate() {}
    public function getAuthor(){}
    public function getAuthorId(){}
    public function getStatus() {}
    public function getUrlPart() {}
    public function getPermalink() {}
    public function getType() {}*/

    public function __call($method, $args = array()) {
        if (method_exists($this, $method))
            return $this->post->$method();
        elseif ($callMethod = array_key_exists_v(strtolower($method), $this->methods)) {
            return $this->post->$callMethod();
        } else {
            throw new \BadMethodCallException(sprintf('The method %s does not exist.', $method));
        }

    }
}