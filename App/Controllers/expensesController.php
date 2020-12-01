<?php 
namespace App\Controllers;
use \App\Models\Expense;
use \App\Models\Account;
use \App\Models\PolicyPayment;
class expensesController extends Controller{
	public function create(){
		$expense = new Expense($this->payload);
		if(!$expense->account->has($expense->amount,$expense->currency)){
			http_response_code(403);
			$this->response(['errors'=>true,'data'=>"La cuenta seleccionada no posee el saldo suficiente para registrar esta salida"]);
		}
		else{
			if($expense->save()){
				$expense->account->withdraw($expense->amount,$expense->currency);
				$this->response(['errors'=>false,'data'=>$expense->to_array(['include'=>['account','category']])]);
			}
			else{
				http_response_code(403);
				$this->response(['errors'=>true,'data'=>"No se pudo crear"]);
			}
		}
	}

	public function index(){
		$result=[
			'expenses'=>[],
			'payments'=>[]
		];

		$expenses = Expense::all(['order'=>'date DESC']);
		foreach($expenses as $expense){
			$expense=$expense->to_array(['include'=>'category']);
			
		}

		$policy_payments = PolicyPayment::all();
		foreach($policy_payments as $payment){
			$p=$payment->to_array();
			$p['date']=\App\Libs\Time::format($p['created_at'],'d-m-Y');
			$p['company']=\App\Models\Client::find([$payment->client_id])->company;
			$p['client']=\App\Models\Client::find([$p['client_id']])->first_name;
			$p['account']=\App\Models\Account::find([$p['account_id']])->name;

			$result['payments'][]=$p;
		}

		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function createPolicyPayment(){
		$payment = new \App\Models\PolicyPayment([
			'user_id'=>$this->current_id,
			'amount'=>$this->payload['amount'],
			'policy_id'=>$this->payload['policy_id'],
			'currency'=>$this->payload['currency'],
			'comment'=>$this->payload['comment'],
			'payment_date'=>$this->payload['payment_date'],
			'account_id'=>$this->payload['account_id']
		]);

		$payment->user_id = $this->current_id;
		$account  = $payment->account;
		if(!$account->has($payment->amount,$payment->currency)){
			http_response_code(403);
			$this->response(['errors'=>true,'data'=>"La cuenta seleccionada no posee el saldo suficiente para registrar esta salida"]);
		}
		else{
			if($payment->save()){
				$client = $payment->policy->client;
				if(isset($this->payload['finance'])){
					$payment->policy->financed = $payment->policy->financed + $payment->amount;
					$payment->policy->save();
				}
				$account->withdraw($payment->amount,$payment->currency);
				$this->response(['errors'=>false,'data'=>'Creado con exito']);
			}
			else{
				$this->response(['errors'=>true,'data'=>"No se pudo crear"]);
			}
		}
	}
}

?>