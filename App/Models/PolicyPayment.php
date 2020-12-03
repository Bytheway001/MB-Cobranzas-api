<?php 

namespace App\Models;
class PolicyPayment extends \ActiveRecord\Model{
	static $belongs_to=[['account'],['policy'],['user']];
	
}
 ?>
