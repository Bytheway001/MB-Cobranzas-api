<?php 
namespace App\Models;
class Transfer extends \ActiveRecord\Model{
	static $belongs_to=[
		['origin','class_name'=>'Account','foreign_key'=>'from'],
		['destiny','class_name'=>'Account','foreign_key'=>'to'],
	];
}

 ?>