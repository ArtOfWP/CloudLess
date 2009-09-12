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
}
?>