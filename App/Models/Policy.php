<?php 
namespace App\Models;

class Policy extends \ActiveRecord\Model{
	static $belongs_to = [
		['client'],
		['plan'],
	];
	static $has_many=[['payments'],['policy_payments']];

	public function company(){
		try{
		return $this->plan->company->to_array();
		}
		catch(\Exception $e){
			print_r($this);
			die();
		}
	}

	public function applyDiscount($amount){
		$this->payed = $this->payed + $amount;
		$this->save();
	}

	public function totals(){
		return [
			'payed'=>$this->totalpayed(),
			'collected'=>$this->totalcollected(),
			'financed'=>$this->totalfinanced()
		];
	}

	public function totalcollected(){
		$cobranzas = $this->payments;
		$total = 0;
		foreach($cobranzas as $cobranza){
			if($cobranza->corrected_with===null && $cobranza->processed===1){
				$total = $total+$cobranza->amount;
			}
			
		}

		return $total;
	}

	public function totalpayed(){
		$policy_payments = $this->policy_payments;
		$total=0;
		foreach($policy_payments as $pp){
			$total = $total + $pp->amount;
		}
		return $total;
	}

	public function totalfinanced(){
		$policy_payments = $this->policy_payments;
		$total = 0;
		foreach($policy_payments as $pp){
			if($pp->payment_type === "Finance"){
				$total = $total+$pp->amount;
			}
		}
		return $total;
	}



}

 ?>

