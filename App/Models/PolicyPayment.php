<?php 

namespace App\Models;
class PolicyPayment extends \ActiveRecord\Model{
	static $belongs_to=[['account'],['client']];

	public function serialize(){
		$payment=$this->to_array(['only'=>['amount','currency']]);
		$payment['date']=$this->created_at->format('d-m-Y');
		$payment['account']=$this->account->name;
		$payment['client']=$this->client->first_name;
		$payment['company']=$this->client->company;
		return $payment;
	}
}
 ?>
