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
				if($cobranza->currency === "BOB"){
					$total = $total + ($cobranza->amount/$cobranza->change_rate);
				}
				else{
					$total = $total+$cobranza->amount;
				}
				
			}
			
		}
		return $total;
	}

	public function totalpayed(){
		$policy_payments = $this->policy_payments;
		$total=0;
		foreach($policy_payments as $pp){
			if($pp->currency==='BOB'){
				$total = $total + round($pp->amount/6.96,2);
			}
			else{
				$total = $total + $pp->amount;
			}
			
		}
		return $total;
	}

	public function totalfinanced($query){
		$policy_payments = $this->policy_payments;
		$total = 0;
		$payed = $this->totalpayed();
		$collected =$this->totalcollected();
		return $payed-$collected<0?0:$payed-$collected;
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


	/* Fecha en la cual comienza la poliza actual */
	public function begginingDate(){
		$now =	new DateTime("now");
		$this_year_renovation_date = new DateTime(date('Y').'-'.$this->effective_date->format('m-d'));
		/* Si aun no ha pasado la fecha de renovacion devolvemos la fecha del año pasado */
		if($now<$this_year_renovation_date){ 
			$date = $this_year_renovation_date->sub(new \DateInterval('P1Y'));
		}
		else{
			$date=$this_year_renovation_date;
		}
		return $date->format('Y-m-d');
	}

 	/* Fechas en las cuales se espera el pago */
	public function getPaymentDates(){
		$last_renovation = new DateTime($this->begginingDate());
		$dates=[$last_renovation->format('Y-m-d')];
		switch($this->frequency){
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
