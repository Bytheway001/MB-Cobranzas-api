<?php 
namespace App\Models;
use \DateTime;
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

	public function  history(){
		$result=[
			'payments'=>[],
			'policy_payments'=>[]
		];
		foreach ($this->payments as $payment) {
			$result['payments'][]=$payment->to_array();
		}

		foreach($this->policy_payments as $pp){
			$result['policy_payments'][]=$pp->to_array();
		}
		return $result;
	}


	public function getLastRenovationDate(){
		$now =	new DateTime("now");
		$date = new DateTime(date('Y').'-'.$this->effective_date->format('m-d'));
		if($now>$this->effective_date){
			$date = $date->sub(new \DateInterval('P1Y'));
		}
		
		return $date->format('Y-m-d');
	}

	public function getPaymentDates(){
		$last_renovation = new DateTime($this->getLastRenovationDate());
		$dates=[$last_renovation->format('Y-m-d')];
		switch($this->frequency){
			case "Annual":
			$dates[]= $last_renovation;
			break;
			case "Semiannual":
			for($i=0;$i<1;$i++){
				$dates[]=$last_renovation->add(new \DateInterval('P6M'))->format('Y-m-d');
			}
			break;

			case "Quarterly":
			for($i=0;$i<3;$i++){
				$dates[]=$last_renovation->add(new \DateInterval('P3M'))->format('Y-m-d');
			}
			break;

			case "Monthly":
			for($i=0;$i<11;$i++){
				$dates[]=$last_renovation->add(new \DateInterval('P1M'))->format('Y-m-d');
			}
			break;
		}	
		return $dates;
	}





}
?>
