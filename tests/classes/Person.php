<?php
class Person extends ActiveRecordBase{
	private $id;
	private $name;
	private $age;
	private $birthday;
	private $introduction;
	private $hcp;
	private $tags;
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}
	/**
	 dbfield:varchar,dblength:40
	 */
	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}
	/**
	dbfield:int
	*/
	public function getAge(){
		return $this->age;
	}

	public function setAge($age){
		$this->age = $age;
	}
	/**
	  dbfield:datetime
	*/
	public function getBirthday(){
		return $this->birthday;
	}

	public function setBirthday($birthday){
		$this->birthday = $birthday;
	}
	/**
	 dbfield:text
	 */
	public function getIntroduction(){
		return $this->introduction;
	}

	public function setIntroduction($introduction){
		$this->introduction = $introduction;
	}
	/**
	 dbfield:decimal(10|3)
	 */
	public function getHcp(){
		return $this->hcp;
	}

	public function setHcp($hcp){
		$this->hcp = $hcp;
	}	
	/**
	field:text,dbrelation:Tag,dbrelationname:taggedpersons,dbindexkind:primary
	*/
	function TagsList(){
		return $this->tags;
	}
	function addTags($tag){
		$this->tags[]=$tag;
	}
}