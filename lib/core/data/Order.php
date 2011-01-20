<?php
class Order{
	var $property;
	var $order;
	public static function ASC($property){
		$o = new Order($property,'ASC');
		return $o;
	}
	public static function DESC($property){
		$o = new Order($property);
		return $o;
	}
	public function Order($property,$order='DESC'){
		$this->property=$property;
		$this->order=$order;
	}
	private function addMark($ct){
		return '`'.$ct.'`';
	}	
	function __toString(){
		global $db_prefix;
		$temp='';
		$p=explode('.',$this->property);
		if(sizeof($p)>1){
			$temp=$this->addMark($db_prefix.strtolower($p[0]));
			$temp.=$this->addMark(strtolower($p[1]));
		}else
			$temp=$this->addMark(strtolower($this->property));
		return $temp.' '.$this->order;
	}
}