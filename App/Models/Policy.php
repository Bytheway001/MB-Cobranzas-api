<?php 
namespace App\Models;

class Policy extends \ActiveRecord\Model{
	static $belongs_to = [
		['client'],
		['plan'],
	];
	static $has_many=[['payments']];

	public function company(){
		try{
		return $this->plan->company->to_array();
		}
		catch(\Exception $e){
			print_r($this);
			die();
		}
	}

}

 ?>