<?php 
namespace App\Models;

class Income extends \ActiveRecord\Model{
	static $belongs_to =[['account']];
}

 ?>