<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Andreas
 * Date: 2013-08-25
 * Time: 15:15
 * To change this template use File | Settings | File Templates.
 */

class ApplicationBase {
    public $app, $dir, $appName;
    private $name;
    private $file;
    /**
     * @var
     */
    private $useOptions;
    /**
     * @var
     */
    private $useInstall;
    /**
     * @var
     */
    private $basename;

    public function __construct($name, $basename, $file, $useOptions=false, $useInstall=false) {
        $this->name = $name;
        $this->file = $file;
        $this->useOptions = $useOptions;
        $this->useInstall = $useInstall;
        $this->basename = $basename;
        if($this->useOptions) {
            $this->options= new Options($this->name);
        }
        Debug::Value($name, $this->name);

    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    public function getInstallDirectory() {
        return dirname($this->file);
    }

    public function getInstallName() {
        return $this->basename;
    }

    //methods
    public function init(){
        $this->onLoadOptions();
    }

    /**
     * @override
     */
    public function onLoadOptions(){}
    public function onInitUpdate(){}
    public function onInit(){}
    public function onUpdate(){}
    public function update(){}
    public function installed(){}
}