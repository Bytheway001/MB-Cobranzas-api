<?php 
namespace App\Models;

class Company extends \ActiveRecord\Model{
	static $has_many =[['plans']];
}

 ?>