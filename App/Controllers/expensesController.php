<?php 
namespace App\Controllers;
use \App\Models\Expense;
use \App\Models\Account;
use \App\Models\PolicyPayment;
class expensesController extends Controller{
	public function create(){
		$this->payload['category']=$this->payload['category_id'];
		unset($this->payload['category_id']);
		$expense = new Expense($this->payload);
		if(!$expense->account->has($expense->amount,$expense->currency)){
			http_response_code(403);
			$this->response(['errors'=>true,'data'=>"La cuenta seleccionada no posee el saldo suficiente para registrar esta salida"]);
		}
		else{
			if($expense->save()){
				$expense->account->withdraw($expense->amount,$expense->currency);
				if($expense->account->type==='Cash'){
					\App\Models\Movement::create(['type'=>"OUT",'description'=>$expense->description,'amount'=>$expense->amount,'currency'=>$expense->currency,'origin'=>$expense->account->id]);
				}
				$this->response(['errors'=>false,'data'=>"Creado con exito"]);
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

		$expenses = Expense::all();
		foreach($expenses as $expense){
			$expense=$expense->to_array();
			$expense['date']=\App\Libs\Time::format($expense['date'],'d-m-Y');
			$expense['account_name']=\App\Models\Account::find([$expense['account_id']])->name;
			$result['expenses'][] =$expense; 
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
		$payment = new \App\Models\PolicyPayment($this->payload);
		$payment->user_id = $this->current_id;
		$client = $payment->client;
		$client->status=$payment->policy_status;
		$client->save();
		$account  = $payment->account;
		if(!$account->has($payment->amount,$payment->currency)){
			http_response_code(403);
			$this->response(['errors'=>true,'data'=>"La cuenta seleccionada no posee el saldo suficiente para registrar esta salida"]);
		}
		else{
			if($payment->save()){
				$account->withdraw($payment->amount,$payment->currency);
				if($payment->account->type === 'Cash'){
					\App\Models\Movement::create(['type'=>"OUT",'description'=>"Pago de Poliza #".$payment->client->policy_number,'amount'=>$payment->amount,'currency'=>$payment->currency,'origin'=>$payment->account->id]);
				}
				$this->response(['errors'=>false,'data'=>'Creado con exito']);

			}
			else{
				$this->response(['errors'=>true,'data'=>"No se pudo crear"]);
			}
		}
	}
}

?>