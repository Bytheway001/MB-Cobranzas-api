<?php 

namespace App\Models;
class Account extends \ActiveRecord\Model{
	public function deposit($amount,$currency){
		$currency = strtolower($currency);
		$this->$currency = $this->$currency+$amount;
		$this->save();
	}

	public function withdraw($amount,$currency){
		try{
			$currency = strtolower($currency);
			if($this->has($amount,$currency)){
				$this->$currency = $this->$currency-$amount;
				$this->save();
				return true;
			}
			else{
				return false;
			}

		}
		catch(\Exception $e){
			return false;
		}
	}

	public function transfer($to,$amount,$currency){
		$currency = strtolower($currency);
		if($this->has($amount,$currency)){
			$this->withdraw($amount,$currency);
			Account::find([$to])->deposit($amount,$currency);
			return true;
		}
		else{
			return false;
		}
	}

	public function convert($to,$amount,$currency,$rate){
		$currency = strtolower($currency);
		$finalCurrency=$currency==='usd'?'bob':'usd';
		if($this->has($amount,$currency)){
			$this->withdraw($amount,$currency);
			$newAmount = $finalCurrency==='usd'?$amount*$rate:$amount/$rate;
			Account::find([$to])->deposit($newAmount,$finalCurrency);
		}
	}

	public function has($amount,$currency){
		$currency = strtolower($currency);
		return $this->$currency >= $amount;
	}

	public function getSaldoAt($date){
		$date = new \DateTime($date);
		$date = $date->format('Y-m-d');
		$fechaInicial = $this->last_balance_date->format('Y-m-d');
		$saldo = ['USD'=>$this->last_balance_usd,'BOB'=>$this->last_balance_bob];
		if($date>$fechaInicial){
			$query = "SELECT * from movimiento_de_cuenta where account_id = $this->id AND DATE(date) between '$fechaInicial' and '$date'";
			$movements = $this->find_by_sql($query);
			foreach($movements as $movement){
				
				$saldo[$movement->currency]=$saldo[$movement->currency]+ $movement->debe - $movement->haber;

			}
			
		}
		else{
			$query = "SELECT * from movimiento_de_cuenta where account_id = $this->id AND DATE(date) between '$date' and '$fechaInicial'";
			$movements = $this->find_by_sql($query);
			foreach($movements as $movement){
				$saldo[$movement->currency]=$saldo[$movement->currency]-$movement->debe + $movement->haber;
			}
		}
		return $saldo;
	}
}
?>
