<?php
namespace CLMVC\Controllers;
use CLMVC\Core\Data\ActiveRecordBase;
use CLMVC\Core\Data\Order;
use CLMVC\Core\Data\Restriction;
use CLMVC\Core\Data\Repo;
use CLMVC\Core\Debug;
use CLMVC\Events\RequestEvent;
use CLMVC\Helpers\Communication;
use CLMVC\Helpers\Http;

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
     * @var bool|Restriction[] Restrictions that should limit the search.
     */
    protected $search_restrictions=false;
    /**
     * @var bool| string the property to search on.
     */
    protected $search_property=false;

    /**
     * @var array Values set on action
     */
    public $bag=array();

    /**
     * Construct the controller setup variables.
     * @param string $viewpath
     */
    public function __construct($viewpath=''){
		parent::__construct($viewpath);
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
        $restrictions = null;
        $message = 'No search: Sliced find all';
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
					$restrictions =	Restriction::LIKE($this->search_property,$this->values['search'],3);
					echo $this->search_property;
				}else if(!empty($this->search_property)){
					$this->getRenderer()->RenderText('You need to configure the search_restrictions property or set a search_property');
					return;
				}
			}
			$message='Search: Sliced find all';
			$this->bag['searchResultTotal']=Repo::total($this->controller,$restrictions);
        }
        Debug::Message($message);
        $this->bag['all']=Repo::slicedFindAll($this->controller,$first,$per_page,$order,$restrictions,null,true);
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
     * @return \CLMVC\Core\Data\ActiveRecordBase
     */
    public function create($redirect=true){
		$this->setRender(false);
        $request = new RequestEvent($_REQUEST);
        $request->loadFromPost($this->crudItem);
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
        $this->setRender(false);
		$id=array_key_exists_v('Id',$this->values);		
		$this->crudItem=Repo::getById(get_class($this->crudItem),$id);
        $request = new RequestEvent($_REQUEST);
        $request->loadFromPost($this->crudItem);
		$this->crudItem->save();
		if($redirect)
			$this->redirect('result=2');
	}

    /**
     * Delete the CRUD item.
     */
    public function delete(){
        $this->setRender(false);
		if(is_array($_POST[strtolower(get_class($this->crudItem))])){
			$ids=$_POST[strtolower(get_class($this->crudItem))];
			foreach($ids as $id){
				$item=Repo::getById(get_class($this->crudItem),$id);
				if($item)
					$item->delete();
			}
            $this->redirect('delete=1');
            return;
        }
        $request = new RequestEvent($_REQUEST);
        $request->loadFromPost($this->crudItem);
        $this->crudItem->delete();
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
