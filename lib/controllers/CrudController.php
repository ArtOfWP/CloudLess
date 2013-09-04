<?php
namespace CLMVC\Controllers;
use ActiveRecordBase;
use CLMVC\Core\Data\Order;
use CLMVC\Core\Data\R;
use CLMVC\Core\Debug;
use CLMVC\Helpers\Communication;
use Repo;

/**
 * Class CrudController
 * @extends BaseController
 * @method onSearchInit
 */
abstract class CrudController extends BaseController {
    /**
     * @var int Number of items per page
     */
    protected $per_page=20;
    /**
     * @var bool|string How items should be sorted
     */
    protected $order=false;
    /**
     * @var bool|string which property should be sorted on
     */
    protected $order_property=false;
    /**
     * @var string  Which way they item should be sorted
     */
    protected $order_way='asc';
    /**
     * @var string The class to connect with the controller
     */
    protected $crudClass;
    /**
     * @var bool|R[] Restrictions that should limit the search.
     */
    protected $search_restrictions=false;
    /**
     * @var bool| string the property to search on.
     */
    protected $search_property=false;

    /**
     * @var bool If controller should be automatic.
     */
    private $automatic;
    /**
     * @var array Values set on action
     */
    public $bag=array();

    /**
     * Construct the controller setup variables.
     * @param bool $automatic
     */
    public function __construct($automatic=true){
		parent::__construct(false);
		$this->automatic=$automatic;
		Debug::Message('Loaded '.$this->controller.' extends Crudcontroller');
	}

    /**
     * Init executes the actions etc.
     */
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

    /**
     * Setup CrudController and execute search init if method exists.
     */
    private function onCrudControllerInit(){
		Debug::message('onInit Crud');
		$this->crudItem=new $this->controller;
		if(array_key_exists('search',$this->values) && method_exists($this,'onSearchInit'))
			$this->onSearchInit();
	}

    /**
     * The action setups bag with created item.
     */
    public function createnew(){
		$this->bag['result']=array_key_exists_v('result',$this->values);		
		if(!isset($this->bag['new']) || empty($this->bag['new']))
			$this->bag['new']=$this->crudItem;
		$this->bag['result']=array_key_exists_v('result',$this->values);
	}

    /**
     * List all CRUD items
     */
    public function listall(){
		$this->bag['delete']=array_key_exists_v('delete',$this->values);
		$this->bag['deletePath']=get_site_url().'/'.strtolower($this->controller).'/delete';
		$per_page=array_key_exists_v('perpage',$this->values);
		$per_page=$per_page?$per_page:$this->per_page;
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
		
		$this->bag['perpage']=$per_page;
		$page=array_key_exists_v('current',$this->values);
		$page=$page?$page:1;
		$this->bag['currentpage']=$page;
		$page=$page>0?$page-1:0;
		$first=$page*$per_page;
		if(array_key_exists('search',$this->values)){
			$restrictions =$this->search_restrictions;
			if(!$restrictions){
				if($this->search_property){
					$restrictions =	R::LIKE($this->search_property,$this->values['search'],3);
					echo $this->search_property;
				}else if(!empty($this->search_property)){
					$this->RenderText('You need to configure the search_restrictions property or set a search_property');
					return;
				}
			}
			Debug::Message('Search: Sliced find all');
			$this->bag['all']=Repo::slicedFindAll($this->controller,$first,$per_page,$order,$restrictions,false,true);
			$this->bag['searchResultTotal']=Repo::total($this->controller,$restrictions);
		}
		else{
			Debug::Message('No search: Sliced find all');
			$this->bag['all']=Repo::slicedFindAll($this->controller,$first,$per_page,$order,null,false,true);
		}
		$this->bag['total']=Repo::total($this->controller);
	}

    /**
     * Setups edited item in bag.
     */
    public function edit(){
		$id=array_key_exists_v('Id',$this->values);		
		$this->bag['result']=array_key_exists_v('result',$this->values);
		Debug::Value('Action','Edit');
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id,true);
		$this->bag['edit']=$this->crudItem;
	}

    /**
     * Create the CRUD item
     * @param bool $redirect
     * @return ActiveRecordBase
     */
    public function create($redirect=true){
		$this->render=false;
		$this->loadFromPost();
		$this->crudItem->save();
		if($redirect) {
			$this->redirect('result=1');
        }
	    return $this->crudItem;
    }

    /**
     * Update the CRUD item
     * @param bool $redirect
     */
    public function update($redirect=true){
		$this->render=false;		
		$id=array_key_exists_v('Id',$this->values);		
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id);		
		$this->loadFromPost();
		$this->crudItem->save();
		if($redirect)
			$this->redirect('result=2');
	}

    /**
     * Delete the CRUD item.
     */
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

    /**
     * Verify the request method.
     * @param $method
     * @return bool
     */
    private function methodIs($method){
		if(Communication::getMethod()==$method)
			return true;
		return false;			
	}
}