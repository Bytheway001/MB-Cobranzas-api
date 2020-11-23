<?php 
namespace App\Models;
use \App\Libs\Translate;
class Expense extends \ActiveRecord\Model{
	static $belongs_to =[
		['account'],
		['cat','class_name'=>'Category','foreign_key'=>'category'],
		['user']
	];
	public function serialize(){
		$expense=$this->to_array(['except'=>['account_id']]);
		$expense['date']=$this->date->format('d-m-Y');
		$expense['office']=Translate::officeName($this->office);
		$expense['account']=$this->account->name;
		$expense['category']=$this->cat->name??"INterno";
		$expense['user']=$this->user?$this->user->name:'None';
		return $expense;
	}
}

?>