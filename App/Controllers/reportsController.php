<?php 
namespace App\Controllers;
use \App\Models\Client;
use \App\Models\Payment;
class reportsController extends Controller{
	public function getReports(){
		$from = $_GET['f']??null;
		$to = $_GET['t']??null;
		$result=[];
		if($from && $to){
				$payments=\App\Models\Payment::all(['conditions'=>['DATE(payment_date) BETWEEN ? AND ?',$this->setDateFormat($from,'Y-m-d'),$this->setDateFormat($to,'Y-m-d')]]);
		}
		else{
			$payments=\App\Models\Payment::all();
		}
	
		$result['caja']=[
			'sc'=>['USD'=>0,'BOB'=>0],
			'lp'=>['USD'=>0,'BOB'=>0],
			'cb'=>['USD'=>0,'BOB'=>0]
		];
		$result['payments']=[];
		$result['bancos']=[
			'1'=>0,
			'2'=>0,
			'3'=>0
		];


		foreach($payments as $payment){
			if($payment->payment_method==='cash_to_agency'){
				if($payment->city === 'sc'){
					$result['caja']['sc'][$payment->currency]+=$payment->amount;
				}
				elseif($payment->city === 'cb'){
					$result['caja']['cb'][$payment->currency]+=$payment->amount;
				}
				else{
					$result['caja']['lp'][$payment->currency]+=$payment->amount;
				}


			}
			if($payment->payment_method === 'transfer_to_foreign_agency' || $payment->payment_method==='transfer_to_local_agency'){
				
				$result['bancos'][$payment->account]+=$payment->amount;
			}
			$result['payments'][]=$payment->serialize();
		}
		$this->response($result);
		die();

	}

	public function payments_per_company(){
		$result=[];
		
		foreach(Payment::all() as $payment){
			$company = $payment->client->company;
			$office= $payment->city;
			if(!isset($result[$company])){
				$result[$company]=[];
			}
			if(!isset($result[$company][$office])){
				$result[$company][$office]=[];
			}
			if(!isset($result[$company][$office][$payment->payment_method])){
				$result[$company][$office][$payment->payment_method]=0;
			}
			
			$result[$company][$office][$payment->payment_method]=$result[$company][$office][$payment->payment_method]+$payment->amount;
			
		}

	$this->response($result);
		die();

	}
	private function setDateFormat($date,$format){
		$date = str_replace('/', '-', $date);
		
		$newDate = date($format, strtotime($date));  
		return $newDate;  
	}
}

?>