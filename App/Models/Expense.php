<?php 
namespace App\Models;
use \App\Libs\Translate;
class Expense extends \ActiveRecord\Model{
	static $belongs_to =[['account']];
	public function serialize(){
		$expense=$this->to_array(['except'=>['account_id']]);
		$expense['date']=$this->date->format('d-m-Y');
		$expense['office']=Translate::officeName($this->office);
		$expense['account']=$this->account->name;
		return $expense;
	}
}

 ?>