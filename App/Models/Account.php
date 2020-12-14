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

}
?>
