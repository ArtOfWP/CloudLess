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
//		Communication::redirectTo(Communication::getReferer());
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
		$uploads=Communication::getUpload($properties);
		print_r($uploads);
		foreach($uploads as $property => $upload){
			$path=PACKAGEPATH.'uploads/'.$upload["name"];
			move_uploaded_file($upload["tmp_name"],$path);
			$values[$property]='/AoiSora/uploads/'.$upload["name"];
			$image = new Resize_Image;
			$image->new_width = 100;
			$image->new_height = 100;
			$image->image_to_resize = $path; // Full Path to the file
			$image->ratio = true; // Keep Aspect Ratio?
			// Name of the new image (optional) - If it's not set a new will be added automatically
			$image->new_image_name = preg_replace('/\.[^.]*$/', '', $upload["name"]);
			/* Path where the new image should be saved. If it's not set the script will output the image without saving it */
			$image->save_folder = PACKAGEPATH.'uploads/thumbs/';
			$process = $image->resize();
			if($process['result'] && $image->save_folder){
				echo 'The new image ('.$process['new_file_path'].') has been saved.';
			}			
		}
		ObjectUtility::setProperties($this->crudItem,$values);
	}

}
?>