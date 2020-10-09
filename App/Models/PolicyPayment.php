<?php 

namespace App\Models;
class PolicyPayment extends \ActiveRecord\Model{
	static $belongs_to=[['account'],['client'],['user']];
	public function serialize(){
		$payment=$this->to_array(['only'=>['amount','currency','policy_status','id']]);
		$payment['date']=$this->created_at->format('d-m-Y');
		$payment['account']=$this->account->name;
		$payment['client']=$this->client->first_name;
		$payment['company']=$this->client->company->name;
		$payment['plan']=$this->client->plan;
		$payment['payment_date']=$this->payment_date->format('d-m-y');

		return $payment;
	}
}
 ?>
