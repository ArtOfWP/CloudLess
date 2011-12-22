<?php
include_once('BaseController.php');
abstract class CrudController extends BaseController{
	protected $perpage=20;
	protected $order=false;
	protected $order_property=false;
	protected $order_way='asc';
	protected $crudClass;
	protected $search_restrictions=false;
	protected $search_property=false;

	private $automatic;
	public $bag=array();
	public function __construct($automatic=true){
		parent::__construct(false);
		$this->automatic=$automatic;
		Debug::Message('Loaded '.$this->controller.' extends Crudcontroller');
	}
	
	public function init(){
		Debug::message('Init CRUD');
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
		Debug::message('onInit Crud');
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
			$this->bag['all']=Repo::slicedFindAll($this->controller,$first,$perpage,$order,$restrictions,false,true);
			$this->bag['searchResultTotal']=Repo::total($this->controller,$restrictions);
		}
		else{
			Debug::Message('No search: Sliced find all');
			$this->bag['all']=Repo::slicedFindAll($this->controller,$first,$perpage,$order,false,false,true);
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
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id);		
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
}