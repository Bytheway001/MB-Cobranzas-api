<?php 
namespace App\Models;

class Income extends \ActiveRecord\Model{
	static $belongs_to =[['account'],['user'],'category'];
	public function serialize(){
		$result=$this->to_array();
		$result['account']=$this->account->name;
		$result['date']=$this->date->format('d-m-Y');
		$result['user']=$this->user?$this->user->name:'Ninguno';
		return $result;
	}
}

 ?>