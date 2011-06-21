<?php
include_once('BaseController.php');
abstract class CrudController extends BaseController{
	protected $crudItem;
	protected $uploadSubFolder;
	protected $width;
	protected $height;
	protected $perpage=20;
	protected $order=false;
	protected $order_property=false;
	protected $order_way='asc';
	protected $crudClass;
	protected $search_restrictions=false;
	protected $search_property=false;
	protected $thumbnails;
	private $automatic;
	public $bag=array();
	public function __construct($automatic=true){
		parent::__construct(false);
		$this->automatic=$automatic;
		Debug::Message('Loaded '.$this->controller.' extends Crudcontroller');
	}
	
	public function init(){
		$this->defaultAction='listall';
		parent::init();
		$this->onCrudControllerInit();
		if($this->automatic){
			Debug::Message('CRUD Executing automatic action ');
			if($this->action){
				Debug::Message('CRUD Pre Automatic Render');
				$this->automaticRender();
			}
			else if($this->methodIs(GET)){
				$id=array_key_exists_v('Id',$this->values);
				if($id)
					$this->executeAction('edit');					
				else if(array_key_exists_v('_method',$this->values))
					$this->executeAction('delete');
				else
					$this->executeAction('listall');
			}
			else if($this->methodIs(POST))
				$this->executeAction('create');
			else if($this->methodIs(PUT))
				$this->executeAction('update');
			else if($this->methodIs(DELETE))
				$this->executeAction('delete');
		}
	}
	private function onCrudControllerInit(){
		$this->crudItem=new $this->controller;
		//TODO deprecated since 11.6
		if(array_key_exists('search',$this->values) && method_exists($this,'on_search_init'))
			$this->on_search_init();
		if(array_key_exists('search',$this->values) && method_exists($this,'onSearchInit'))
			$this->onSearchInit();
	}
	public function createnew(){
		$this->bag['result']=array_key_exists_v('result',$this->values);		
		if(!isset($this->bag['new']) || empty($this->bag['new']))
			$this->bag['new']=$this->crudItem;
		$this->bag['result']=array_key_exists_v('result',$this->values);
	}
	public function listall(){
		$this->bag['delete']=array_key_exists_v('delete',$this->values);
		$this->bag['deletePath']=get_site_url().'/'.strtolower($this->controller).'/delete';
		$perpage=array_key_exists_v('perpage',$this->values);
		$perpage=$perpage?$perpage:$this->perpage;
		$order=$this->order;
		
		if(!$order){
			$order_property=array_key_exists_v('property',$this->values);
			$order_property=$order_property?$order_property:$this->order_property;
	
			$order_way=array_key_exists_v('way',$this->values);
			$order_way=$order_way?$order_way:$this->order_way;
	
			if($order_property){
				if(strtolower($order_way)=='desc')
					$order=Order::DESC($order_property);
				else
					$order=Order::ASC($order_property);
			}
		}
		
		$this->bag['perpage']=$perpage;		
		$page=array_key_exists_v('current',$this->values);
		$page=$page?$page:1;
		$this->bag['currentpage']=$page;
		$page=$page>0?$page-1:0;
		$first=$page*$perpage;
		
		if(array_key_exists('search',$this->values)){
			$restrictions=$this->search_restrictions;
			if(!$restrictions){
				if($this->search_property){
					$restrictions=	R::LIKE($this->search_property,$this->values['search'],3);										
					echo $this->search_property;
				}else if(!empty($this->search_property)){
					$this->RenderText('You need to configure the search_restrictions property or set a search_property');
					return;
				}
			}
			Debug::Message('Search: Sliced find all');			
			$this->bag['all']=Repo::slicedFindAll($this->controller,$first,$perpage,$order,$restrictions);
			$this->bag['searchResultTotal']=Repo::total($this->controller,$restrictions);
		}
		else{
			Debug::Message('No search: Sliced find all');
			$this->bag['all']=Repo::slicedFindAll($this->controller,$first,$perpage,$order);
		}
		$this->bag['total']=Repo::total($this->controller);
	}
	public function edit(){
		$id=array_key_exists_v('Id',$this->values);		
		$this->bag['result']=array_key_exists_v('result',$this->values);
		Debug::Value('Action','Edit');
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id,true);
		$this->bag['edit']=$this->crudItem;
	}
	public function create($redirect=true){
		Debug::message('Creating ... ');
		$this->render=false;
		$this->loadFromPost();
		$this->crudItem->save();
		if($redirect)
			$this->redirect('result=1');
		else
			return $this->crudItem;
	}

	public function update($redirect=true){
		$this->render=false;		
		$id=array_key_exists_v('Id',$this->values);		
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id,true);		
		$this->loadFromPost();
		$this->crudItem->save();
		if($redirect)
			$this->redirect('result=2');
	}
	public function delete(){
		$this->render=false;		
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
				if(isset($this->thumbnails[$property]) && $this->thumbnails[$property]=='thumb')
					$path=UPLOADS_DIR.$folder.'thumbs/'.$upload["name"];
				else
					$path=UPLOADS_DIR.$folder.$upload["name"];
				
				$path=UPLOADS_DIR.$folder.$upload["name"];
				move_uploaded_file($upload["tmp_name"],$path);
				chmod($path, octdec(644));				
				$values[$property]=$upload["name"];
				if(isset($this->thumbnails[$property]) && $this->thumbnails[$property][0]=='create'){
					$image = new Resize_Image;
					$image->new_width = $width;
					$image->new_height = $height;
					$image->image_to_resize = $path;
					$image->ratio = true;
					$image->new_image_name = preg_replace('/\.[^.]*$/', '', $upload["name"]);
					$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
					$values[$this->thumbnails[$property][1]]='thumbs/'.$upload["name"];
					$process = $image->resize();
					chmod($process['new_file_path'], octdec(644));
				}
			}else{
				if(!isset($this->values[$property.'_hasimage']) && empty($values[$property])){
					$values[$property]='';
				}
				else{
					if(strpos($this->values[$property.'_hasimage'],'ttp')==1){
						Debug::Message('HAS IMAGE LINK '.$property);
						$url = $this->values[$property.'_hasimage'];
						$name=str_replace(' ','-',urldecode(basename($url)));
						if(isset($this->thumbnails[$property]) && $this->thumbnails[$property]=='thumb')
							$path=UPLOADS_DIR.$folder.'thumbs/'.$name;
						else
							$path=UPLOADS_DIR.$folder.$name;
						$values[$property]=$name;
						
						Http::save_image($url,$path);
						if(isset($this->thumbnails[$property]) && $this->thumbnails[$property][0]=='create'){
							Debug::Message('CREATE THUMBNAIL');
							$image = new Resize_Image;
							$image->new_width = $width;
							$image->new_height = $height;
							$image->image_to_resize = $path; // Full Path to the file
							$image->ratio = true; // Keep Aspect Ratio?
							$image->new_image_name = preg_replace('/\.[^.]*$/', '', $name);
							$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
							$values[$this->thumbnails[$property][1]]='thumbs/'.$name;
							$process = $image->resize();
							chmod($process['new_file_path'], octdec(644));							
						}
					}else{
						Debug::Message('HAS IMAGE '.$property);
						Debug::Value('Thumbnails',$this->thumbnails);
						if(isset($this->thumbnails[$property]) && $this->thumbnails[$property][0]=='create'){
							Debug::Message('CREATE THUMBNAIL');
							$url = $this->values[$property.'_hasimage'];
							$name=str_replace(' ','-',urldecode(basename($url)));							
							$path=UPLOADS_DIR.$folder.$name;
							$image = new Resize_Image;
							$image->new_width = $width;
							$image->new_height = $height;
							$image->image_to_resize = $path; // Full Path to the file
							$image->ratio = true; // Keep Aspect Ratio?
							// Name of the new image (optional) - If it's not set a new will be added automatically
							$image->new_image_name = preg_replace('/\.[^.]*$/', '', $name);
							// Path where the new image should be saved. If it's not set the script will output the image without saving it 
							$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
							$values[$this->thumbnails[$property][1]]='thumbs/'.$name;
							$process = $image->resize();
							chmod($process['new_file_path'], octdec(644));							
						}						
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
				$values=explode(',',trim($value," ,."));
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
					if(is_array($value))
						foreach($value as $val){
							$object=Repo::getById($dbrelation,$val);
							$objects[]=$object;
						}
					else{	
						$object=Repo::getById($dbrelation,$value);
						$objects[]=$object;
					}
				}
				
			ObjectUtility::addToArray($this->crudItem,str_ireplace("_list","",$method),$objects);
		}
	}

}