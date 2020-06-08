<?php 
namespace App\Controllers;
use \App\Models\Payment;
class paymentsController extends Controller{
	public function create(){
		$expense = new Expense($this->payload);
		print_r($expense);
		die();
		if($expense->save()){
			$this->response(['errors'=>false,'data'=>"Creado con exito"]);
		}
		else{
				$this->response(['errors'=>true,'data'=>"No se pudo crear"]);
		}
	}

	public function index(){
		$result=[];
		$payments = Payment::all();
		foreach($payments as $payment){
			$payment=$payment->to_array();
			$client = \App\Models\Client::find([$payment['client_id']]);
			$payment['payment_date']=\App\Libs\Time::format($payment['payment_date'],'d-m-Y');
			$payment['client']=$client->first_name;
			$payment['collector']=$client->collector->name;
			$result[] = $payment;
		}

		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function validate($id){
		$payment=Payment::find([$id]);
		$payment->processed=1;
		$payment->save();
		$this->response(['errors'=>false,'data'=>'Validated Successfully']);
	}
}

 ?>