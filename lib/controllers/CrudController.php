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
			$action=array_key_exists_v(ACTIONKEY,$this->values);
			if($action)
				$this->$action();
			else if($this->methodIs(GET)){
				$id=array_key_exists_v('Id',$this->values);
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
		$this->bag['result']=array_key_exists_v('result',$this->values);
		$this->Render($this->controller,'createnew');		
	}
	function listall(){
		$this->bag['all']=Repo::findAll(get_class($this->crudItem),true);
		$this->Render($this->controller,'listall');
	}
	function edit(){
		$id=array_key_exists_v('Id',$this->values);		
		Debug::Value('Action','Edit');
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id,true);
//		$this->loadFromPost();
		$this->bag['edit']=$this->crudItem;//$this->crudItem->getById($id);
		$this->Render($this->controller,'edit');
	}
	function create(){
		$this->loadFromPost();
		$this->crudItem->create();
		$this->redirect('&result=1');
	}
	private function redirect($query=false){
		if(defined('NOREDIRECT') && NOREDIRECT)
			return;
		$redirect=Communication::useRedirect();
		if($redirect)
			if(strtolower($redirect)=='referer')
				Communication::redirectTo(Communication::getReferer(),$query);
			else
				Communication::redirectTo($redirect,$query);
	}
	function update(){
		$id=array_key_exists_v('Id',$this->values);		
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id,false);		
		$this->loadFromPost();
		$this->crudItem->update();
		$this->redirect();	}
	function delete(){
		$this->loadFromPost();
		$this->crudItem->delete();
		$this->redirect();
	}	
	private function methodIs($method){
		if(Communication::getMethod()==$method)
			return true;
		return false;			
	}
	private function loadFromPost(){
		$properties = ObjectUtility::getPropertiesAndValues($this->crudItem);
		Debug::Message('LoadFromPost');
		$arrvalues=$this->values;
		Debug::Value('Post',$arrvalues);

		//		Debug::Value('Uploaded',Communication::getUpload($properties));
		$values=Communication::getFormValues($properties);
		Debug::Value('Loaded properties/values for '.get_class($this->crudItem),$values);		
		$arrprop=ObjectUtility::getArrayPropertiesAndValues($this->crudItem);
		$lists=array_search_key('_list',$arrvalues);
		Debug::Value('Loaded listvalues from post',$lists);
		$uploads=Communication::getUpload($properties);
		foreach($uploads as $property => $upload){
			if(strlen($upload["name"])>0){
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
//			if($process['result'] && $image->save_folder){
//				echo 'The new image ('.$process['new_file_path'].') has been saved.';
//			}			
			}else{
				if(!isset($this->values[$property.'_hasimage']))
					$values[$property]='';
			}
		}
		ObjectUtility::setProperties($this->crudItem,$values);
		foreach($lists as $method => $value){
			$settings=ObjectUtility::getCommentDecoration($this->crudItem,str_ireplace("_list","",$method).'List');
			$dbrelation=array_key_exists_v('dbrelation',$settings);
			if(sizeof($value)<2)
				continue;
			$values=explode(',',$value);
			$objects=array();
			foreach($values as $value)
				if($dbrelation){
					$object= new $dbrelation;
					$object->setName($value);
					$object->save();
					$objects[]=$object;
				}
			ObjectUtility::addToArray($this->crudItem,str_ireplace("_list","",$method),$objects);
		}
	}

}
?>