<?php 
namespace App\Models;
function setDateFormat($date,$format){
	$newDate = date($format, strtotime($date));  
	return $newDate;  
}
class Client extends \ActiveRecord\Model{
	static $belongs_to=[['agent'],['collector','class_name'=>'User','foreign_key'=>'collector_id']];
	static $has_many=[['payments']];
	public function serialize(){
		$r=$this->to_array();
		$r['effective_date']=setDateFormat($this->effective_date,'d-m-Y');
		$r['renovation_date']=setDateFormat($this->renovation_date,'d-m-Y');
		$r['agent']=$this->agent->name;
		$r['collector']=$this->collector->name;
		return $r;
	}


}


?>