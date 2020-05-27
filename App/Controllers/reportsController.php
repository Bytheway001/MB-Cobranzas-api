<?php 
namespace App\Controllers;
use \App\Models\Client;
use \App\Models\Payment;
class reportsController extends Controller{
	public function getReports(){
		$from = $_GET['f']??null;
		$to = $_GET['t']??null;
		$result=[];

		$accounts=\App\Models\Account::all();
		foreach($accounts as $account){
			$result['accounts'][]=$account->to_array();
		}
		$payments=Payment::all();
		foreach($payments as $payment){
			$p=$payment->to_array();
			$p['account']=\App\Models\Account::find([$p['account']])->name;
			$client=Client::find([$p['client_id']]);
			$p['client']=$client->name;
			$p['collector']=$client->collector->name;
			$result['payments'][]=$p;

		}


		if($from && $to){
				$payments=\App\Models\Payment::all(['conditions'=>['DATE(payment_date) BETWEEN ? AND ?',$this->setDateFormat($from,'Y-m-d'),$this->setDateFormat($to,'Y-m-d')]]);
		}
		else{
			$payments=\App\Models\Payment::all();
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