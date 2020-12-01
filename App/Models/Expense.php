<?php 
namespace App\Models;
use \App\Libs\Translate;
class Expense extends \ActiveRecord\Model{
	static $belongs_to =[
		['account'],
		['category'],
		['user']
	];
	
}

?>