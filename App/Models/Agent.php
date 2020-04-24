<?php 
namespace App\Models;

class Agent extends \ActiveRecord\Model{
	static $has_many=[['clients']];
}

 ?>