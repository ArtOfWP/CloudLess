<?php

abstract class CrudController extends BaseController{
	protected $crudItem;
	public $bag=array();
	function CrudController($automatic=true){
		parent::BaseController(false);
		$this->crudItem=new $this->controller;
		Debug::Message('Loaded '.$this->controller.' extends Crudcontroller');
		
		if($automatic){
			Debug::Message('Executing automatic action');
			$action=array_key_exists_v(ACTIONKEY,$_GET);
			if($action)
				$this->$action();
			else if($this->methodIs(GET)){
				$id=array_key_exists_v('Id',$_GET);
				if($id)
					$this->edit($id);
				else
					$this->listall();
			}
			else if($this->methodIs(POST))
				$this->create();
			else if($this->methodIs(PUT))
				$this->update();
			else if($this->methodIs(DELETE))
				$this->delete();		
		}
	}
	function createnew(){
		$this->bag['new']=$this->crudItem;
		$this->Render($this->controller,'createnew');		
	}
	function listall(){
		$this->bag['all']=$this->crudItem->findAll();
		$this->Render($this->controller,'listall');
	}
	function edit($id){
		$this->loadFromPost();
		$this->bag['edit']=$this->crudItem->getById($id);
		$this->Render($this->controller,'edit');
	}
	function create(){
		$this->loadFromPost();
		$this->crudItem->create();
//		if(Communication::useRedirect()=='referer')
//		$goback=wp_get_referer();
//		wp_redirect($gopack);
//			Communication::redirectTo(Communication::getReferer());
	}
	function update(){
		$this->loadFromPost();
		$this->crudItem->update();
	}
	function delete(){
		$this->loadFromPost();
		$this->crudItem->delete();
		Communication::redirectTo(Communication::getReferer(),array('delete'=>'success'));		
	}	
	private function methodIs($method){
		if(Communication::getMethod()==$method)
			return true;
		return false;			
	}
	private function loadFromPost(){
		$properties = ObjectUtility::getPropertiesAndValues($this->crudItem);
		Debug::Message('LoadFromPost');
		Debug::Value('Loaded properties/values from'.get_class($this->crudItem),$properties);
		Debug::Value('Uploaded',Communication::getUpload($properties));
		$values=Communication::getFormValues($properties);
		Debug::Value('Loaded values from post',$values);
		print_r(Communication::getUpload($properties));
		ObjectUtility::setProperties($this->crudItem,$values);
	}

}
?>