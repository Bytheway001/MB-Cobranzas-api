<?php 
namespace App\Models;

class Plan extends \ActiveRecord\Model{
	static $belongs_to = [
		['company']
	];
	static $has_many=['policies'];

	public function print_data(){
		print_r($this);
		die();
	}
}

 ?>