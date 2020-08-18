<?php 
namespace App\Models;
class User extends \ActiveRecord\Model{
	static $belongs_to = [['account']];
}

 ?>