<?php 
namespace App\Controllers;
use \App\Models\Expense;
use \App\Models\Account;
use \App\Models\PolicyPayment;
class expensesController extends Controller{
	public function create(){
		$expense = new Expense($this->payload);
		$payingAccount = Account::find([$expense->account]);

		$currency = strtolower($expense->currency);
		if($payingAccount->$currency<$expense->amount){
			http_response_code(403);
			$this->response(['errors'=>true,'data'=>"La cuenta seleccionada no posee el saldo suficiente para registrar esta salida"]);
		}
		else{
			if($expense->save()){

				$payingAccount->$currency = $payingAccount->$currency-$expense->amount;

				$payingAccount->save();
				$this->response(['errors'=>false,'data'=>"Creado con exito"]);
			}
			else{
				$this->response(['errors'=>true,'data'=>"No se pudo crear"]);
			}
		}
		
	}

	public function index(){
		$result=[
			'expenses'=>[],
			'payments'=>[]
		];

		$expenses = Expense::all();
		foreach($expenses as $expense){
			$expense=$expense->to_array();
			$expense['date']=\App\Libs\Time::format($expense['date'],'d-m-Y');
			$expense['account_name']=\App\Models\Account::find([$expense['account']])->name;
			$result['expenses'][] =$expense; 
		}

		$policy_payments = PolicyPayment::all();
		foreach($policy_payments as $payment){
			$p=$payment->to_array();
			$p['date']=\App\Libs\Time::format($p['created_at'],'d-m-Y');
			$p['company']=\App\Models\Client::find([$payment->client])->company;
			$p['client']=\App\Models\Client::find([$p['client']])->first_name;
			$p['account']=\App\Models\Account::find([$p['account']])->name;

			$result['payments'][]=$p;
		}

		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function createPolicyPayment(){
		$payment = new \App\Models\PolicyPayment($this->payload);
		$client = \App\Models\Client::find([$payment->client]);
		$client->status=$payment->policy_status;
		$client->save();
		$account  = Account::find([$payment->account]);
		$currency = strtolower($payment->currency);
		if($account->$currency<$payment->amount){
			$this->response(['errors'=>true,'data'=>'Saldo insuficiente para realizar este pago']);
		}
		else{
			if($payment->save()){
				$account->$currency = $account->$currency-$payment->amount;
				$account->save();
				$this->response(['errors'=>false,'data'=>'Creado con exito']);
			}
			else{
				$this->response(['errors'=>true,'data'=>"No se pudo crear"]);
			}
		}
		
	}
}

?>