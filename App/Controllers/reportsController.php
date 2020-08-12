<?php 
namespace App\Controllers;
use \App\Models\Client;
use \App\Models\Payment;
class reportsController extends Controller{
	public function getReports(){
		$from = $_GET['f']??null;
		$to = $_GET['t']??null;
		$result=[
			'expenses'=>[],
			'policy_payments'=>[],
			'payments'=>[],
			'checks'=>[],
		];


		if($from && $to){
			
			$payments=\App\Models\Payment::all(['conditions'=>['DATE(payment_date) BETWEEN ? AND ?',$this->setDateFormat($from,'Y-m-d'),$this->setDateFormat($to,'Y-m-d')]]);
			$expenses=\App\Models\Expense::all(['order'=>'date DESC','conditions'=>['DATE(date) BETWEEN ? AND ?',$this->setDateFormat($from,'Y-m-d'),$this->setDateFormat($to,'Y-m-d')]]);
			$policy_payments=\App\Models\PolicyPayment::all(['conditions'=>['DATE(created_at) BETWEEN ? AND ?',$this->setDateFormat($from,'Y-m-d'),$this->setDateFormat($to,'Y-m-d')]]);
		}

		else{
			$payments=\App\Models\Payment::all(['order'=>'payment_date DESC']);
			$expenses=\App\Models\Expense::all(['order'=>'date DESC']);
			$policy_payments=\App\Models\PolicyPayment::all(['order'=>'created_at DESC']);
		}

		foreach($payments as $payment){
			$result['payments'][]=$payment->serialize();
		}
		foreach($expenses as $expense){
			$result['expenses'][]=$expense->serialize();
		}
		foreach($policy_payments as $policy_payment){
			$result['policy_payments'][]=$policy_payment->serialize();
		}
		foreach(\App\Models\Check::all(['conditions'=>['status = ?','Abonado en cuenta']]) as $check){
			$c = $check->to_array();

			$c['client']=$check->client->first_name;
			
			if($check->status=='Abonado en cuenta'){
				$c['collected']=$check->collected_at->format('d-m-Y');
			}
			else{
				$c['collected']='--';
			}
			$result['checks'][]=$c;


		}

		$this->response($result);


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

	public function accountMovements($id){
		try{
			$movements = \App\Models\Movement::all(['order'=>'date','conditions'=>['origin = ? or destiny = ?',$id,$id]]);
		}
		catch(\Exception $e){
			print_r(\App\Models\Movement::table()->conn->last_query);
			die();
		}
		
		
		$result=[];
		foreach($movements as $movement){
			$m = $movement->to_array();
			$m['date']=$movement->date->format('d-m-Y');
			$result[]=$m;
		}
		$this->response(['errors'=>false,'data'=>$result]);
	}
}

?>