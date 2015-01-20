<?php
namespace CLMVC\Core\Data;
/**
 * Class Order
 */
class Order{
    /**
     * @var string property of the class or table
     */
    var $property;
    /**
     * @var string ASC,DESC order of the class or table
     */
    var $order;

    /**
     * Create an ASC order
     * @param string $property property of the class or table
     * @return Order
     */
    public static function ASC($property){
		$o = new Order($property,'ASC');
		return $o;
	}

    /**
     * Create an DESC order
     * @param string $property property of the class or table
     * @return Order
     */
	public static function DESC($property){
		$o = new Order($property);
		return $o;
	}
    /**
     * Create an order
     * @param string $property property of the class or table
     * @param string $order ASC, DESC
     */
    public function Order($property,$order='DESC'){
		$this->property=$property;
		$this->order=$order;
	}

    /**
     * Surround property with MySQL marks
     * @param string $ct
     * @return string
     */
    private function addMark($ct){
		return '`'.$ct.'`';
	}

    /**
     * Convert to string
     * @return string
     */
    function __toString(){
		global $db_prefix;
		$temp='';
		$p=explode('.',$this->property);
		if(sizeof($p)>1){
			$temp=$this->addMark($db_prefix.strtolower($p[0]));
			$temp.=$this->addMark(strtolower($p[1]));
		}elseif($this->property)
			$temp=$this->addMark(strtolower($this->property));
		return $temp.' '.$this->order;
	}

    /**
     * Create random order
     * @param string $number
     * @return Order
     */
    public static function Random($number='') {
        $o= new Order(false,'RAND('.$number.')');
        return $o;
    }
}