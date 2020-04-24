<?php 
namespace App\Models;

class Payment extends \ActiveRecord\Model{
	static $belongs_to=['client'];
}

 ?>