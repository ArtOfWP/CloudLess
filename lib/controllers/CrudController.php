<?php
include_once('BaseController.php');
abstract class CrudController extends BaseController{
	protected $crudItem;
	protected $uploadSubFolder;
	protected $width;
	protected $height;
	protected $perpage;
	protected $order=false;
	protected $order_property;
	protected $order_way;
		
	public $bag=array();
	function CrudController($automatic=true){
		parent::BaseController(false);
		$this->crudItem=new $this->controller;
		Debug::Message('Loaded '.$this->controller.' extends Crudcontroller');
		
		if($automatic){
			Debug::Message('CRUD Executing automatic action');
			if($this->action){
				Debug::Message('CRUD Pre Automatic Render');
				$this->automaticRender();
			}
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
		if(!isset($this->bag['new']) || empty($this->bag['new']))
			$this->bag['new']=$this->crudItem;
		$this->bag['result']=array_key_exists_v('result',$this->values);
		$this->Render($this->controller,'createnew');		
	}
	function listall(){
		$this->bag['delete']=array_key_exists_v('delete',$this->values);
		$this->bag['deletePath']=get_site_url().'/ShopCategory/delete';
		$perpage=array_key_exists_v('perpage',$this->values);
		$perpage=$perpage?$perpage:$this->perpage;
		$order=$this->order;
		
		if(!$order){
			$order_property=array_key_exists_v('property',$this->values);
			$order_property=$order_property?$order_property:$this->order_property;
	
			$order_way=array_key_exists_v('way',$this->values);
			$order_way=$order_way?$order_way:$this->order_way;
	
			if(strtolower($order_way)=='desc')
				$order=Order::DESC($order_property);
			else
				$order=Order::ASC($order_property);
		}
		
		$this->bag['perpage']=$perpage;		
		$page=array_key_exists_v('current',$this->values);
		$page=$page?$page:1;
		$this->bag['currentpage']=$page;
		$page=$page>0?$page-1:0;
		$first=$page*$perpage;
		
		if(array_key_exists('search',$this->values)){
			$this->bag['all']=Repo::slicedFindAll($this->crudItem,$first,$perpage,$order,R::LIKE('Name',$this->values['search']));
			$this->bag['total']=Repo::total('ShopCategory',R::LIKE('Name',$this->values['search']));
		}
		else{
			$this->bag['all']=Repo::slicedFindAll($this->crudItem,$first,$perpage,$order);
			$this->bag['total']=Repo::total('ShopCategory');			
		}
		
		$this->Render($this->controller,'listall');
	}
	function edit(){
		$id=array_key_exists_v('Id',$this->values);		
		Debug::Value('Action','Edit');
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id,true);
		$this->bag['edit']=$this->crudItem;
		$this->Render($this->controller,'edit');
	}
	function create($redirect=true){
		$this->loadFromPost();
		$this->crudItem->save();
		if($redirect)
			$this->redirect('result=1');
		else
			return $this->crudItem;
	}
	function redirect($query=false){
		if(defined('NOREDIRECT') && NOREDIRECT)
			return;
		$redirect=Communication::useRedirect();
		if($redirect)
			if(strtolower($redirect)=='referer')
				Communication::redirectTo(str_replace('&result=1','',Communication::getReferer()),$query);
			else
				Communication::redirectTo($redirect,$query);
	}
	function update($redirect=true){
		$id=array_key_exists_v('Id',$this->values);		
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id,true);		
		$this->loadFromPost();
		$this->crudItem->save();
		if($redirect)
			$this->redirect('result=1');
	}
	function delete(){
		if(is_array($_POST[strtolower(get_class($this->crudItem))])){
			$ids=$_POST[strtolower(get_class($this->crudItem))];
			foreach($ids as $id){
				$item=Repo::getById(get_class($this->crudItem),$id);
				if($item)
					$item->delete();
			}
		}else{
			$this->loadFromPost();
			$this->crudItem->delete();
		}		
		$this->redirect('delete=1');
	}	
	private function methodIs($method){
		if(Communication::getMethod()==$method)
			return true;
		return false;			
	}
	private function loadFromPost(){
		$folder='';
		$width=100;
		$heigh=100;
		if($this->uploadSubFolder)
			$folder=$this->uploadSubFolder.'/';
		if($this->width)
			$width=$this->width;
		if($this->height)
			$height=$this->height;			
		$properties = ObjectUtility::getPropertiesAndValues($this->crudItem);
		Debug::Message('LoadFromPost');
		$arrvalues=$this->values;
		Debug::Value('Post',$arrvalues);

		//		Debug::Value('Uploaded',Communication::getUpload($properties));
		$values=Communication::getFormValues($properties);
		$values=array_map('stripslashes',$values);
		Debug::Value('Loaded properties/values for '.get_class($this->crudItem),$values);		
		$arrprop=ObjectUtility::getArrayPropertiesAndValues($this->crudItem);
		$lists=array_search_key('_list',$arrvalues);
		Debug::Value('Loaded listvalues from post',$lists);
		$uploads=Communication::getUpload($properties);
		foreach($uploads as $property => $upload){
			Debug::Message('CHECKING UPLOADS');
			if(strlen($upload["name"])>0){
				Debug::Message('FOUND UPLOAD');
				$path=UPLOADS_DIR.$folder.$upload["name"];
				move_uploaded_file($upload["tmp_name"],$path);
				$values[$property]=$upload["name"];
				$image = new Resize_Image;
				$image->new_width = $width;
				$image->new_height = $height;
				$image->image_to_resize = $path;
				$image->ratio = true;
				$image->new_image_name = preg_replace('/\.[^.]*$/', '', $upload["name"]);
				$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
				$process = $image->resize();		
			}else{
				if(!isset($this->values[$property.'_hasimage'])){
					$values[$property]='';					
				}
				else{
					if(strpos($this->values[$property.'_hasimage'],'ttp')==1){			
						$url = $this->values[$property.'_hasimage'];
						$name=str_replace(' ','-',urldecode(basename($url)));
						$path=UPLOADS_DIR.$folder.$name;					
						$values[$property]=$name;
						
						Http::save_image($url,$path);					
						$image = new Resize_Image;
						$image->new_width = $width;
						$image->new_height = $height;
						$image->image_to_resize = $path; // Full Path to the file
						$image->ratio = true; // Keep Aspect Ratio?
						// Name of the new image (optional) - If it's not set a new will be added automatically
						$image->new_image_name = preg_replace('/\.[^.]*$/', '', $name);
						// Path where the new image should be saved. If it's not set the script will output the image without saving it 
						$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
						$process = $image->resize();
					}
				}
			}
		}
		ObjectUtility::setProperties($this->crudItem,$values);
		foreach($lists as $method => $value){
			Debug::Value($method,$value);
			$settings=ObjectUtility::getCommentDecoration($this->crudItem,str_ireplace("_list","",$method).'List');
			$dbrelation=array_key_exists_v('dbrelation',$settings);
			Debug::Value($method,$dbrelation);
			$field=array_key_exists_v('field',$settings);
			$objects=array();	
			if($field=='text'){
				$values=explode(',',$value);
				if(sizeof($values)==0)
					continue;
				foreach($values as $value){
					if($dbrelation && $field=='text'){
						$object= new $dbrelation;
						$object->setName(trim($value));
						$object->save();
						$objects[]=$object;
					}
				}
			}
			else if($dbrelation){
					$object=Repo::getById($dbrelation,$value);
					$objects[]=$object;					
				}
				
			ObjectUtility::addToArray($this->crudItem,str_ireplace("_list","",$method),$objects);
		}
	}

}