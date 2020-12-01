<?php 
namespace App\Models;
class Check extends \ActiveRecord\Model{
	static $belongs_to = [['client'],['account']];
}

?>