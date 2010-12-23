<?php
class Tag extends ActiveRecordBase{
	private $id;
	private $name;
	private $slug;
	private $total;
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}	
	function setSlug($slug){
		$slug=str_replace(' ','-',$slug);
		$this->slug=$slug;
	}

	/**
	 dbindexkind:unique,dbindexname:tagslug
	 */
	function getSlug(){
		return $this->slug;
	}
	 /**
	 dbindexkind:index,dbindexname:shoptagtotal
	 */
	function getTotalTagged(){
		return $this->total;
	}
	function setTotalTagged($total){
		$this->total=$total;
	}	
}