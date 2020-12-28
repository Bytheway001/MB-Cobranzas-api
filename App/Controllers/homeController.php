<?php 
namespace App\Controllers;
use \Core\View;
use \App\Models\User;
use \App\Models\Account;
use \App\Models\Company;
use \App\Models\Plan;
class homeController extends Controller{
	public function index(){
		$this->response(['errors'=>false,'data'=>'Welcome to MB-Cobranzas']);
	}
	public function listAccounts(){
		$result=[];
		$accounts = \App\Models\Account::all(['order'=>'name']);
		foreach($accounts as $account){
			$result[]=['id'=>$account->id,'name'=>$account->name,'usd'=>$account->usd,'bob'=>$account->bob,'type'=>$account->type];
		}

		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function listChecks(){
		$result=[];
		$checks =\App\Models\Check::all(['conditions'=>['status != ?',"Abonado en cuenta"]]);
		foreach($checks as $check){
			$arr= $check->to_array(['include'=>'client']);
			
			$result[] =$arr;

		}
		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function listCategories(){
		$result=[];
		$categories = \App\Models\Category::all(['order'=>'name ASC']);
		foreach($categories as $category){
			$result[]=$category->to_array();
		}

		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function auth(){
		$user=User::find_by_email($_GET['id']);
		$response=$user->to_array(['include'=>'account']);

		$this->response(['errors'=>false,'data'=>$response]);
	}

	public function convert(){
		$currencies = explode('/',$this->payload['type']);
		$coinFrom = $currencies[0];
		$coinTo=  $currencies[1];
		$amount = $this->payload['amount'];


		$accountFrom = Account::find([$this->payload['from']]);
		if($coinFrom==='USD'){
			$converted = $amount * $this->payload['rate'];
		}
		else{
			$converted = $amount / $this->payload['rate'];
		}

		if($accountFrom->has($amount,$coinFrom)){
			$accountTo = Account::find([$this->payload['to']]);
			$accountTo->deposit($converted,$coinTo);
			$accountFrom->withdraw($amount,$coinFrom);
			\App\Models\Expense::create([
				'user_id'=>$this->current_id,
				'bill_number'=>'S/N',
				'description'=>'Cambio de divisas',
				'currency'=>$coinFrom,
				'amount'=>$amount,
				'account_id'=>$accountFrom->id,
				'category_id'=>99,
				'date'=>date('Y-m-d H:i:s')
			]);
			\App\Models\Income::create([
				'user_id'=>$this->current_id,
				'description'=>'Cambio de divisas',
				'category_id'=>99,
				'currency'=>$coinTo,
				'amount'=>$converted,
				'account_id'=>$accountTo->id,
				'date'=>date('Y-m-d H:i:s')

			]);
			
			$this->response(['errors'=>false,'data'=>'Conversion Exitosa']);
		}
		else{
			http_response_code(401);
			$this->response(['errors'=>true,'data'=>'Saldo insuficiente en cuenta saliente']);
		}
	}

	public function listCompanies(){
		$result=[];
		$companies = Company::all();
		foreach($companies as $company){
			$result[]=$company->to_array(['include'=>'plans']);
		}
		$this->response(['errors'=>false,'data'=>$result]);
	}
	public function listPlans($company_id){
		$result=[];
		$company = Company::find([$company_id]);

		foreach($company->plans as $plan){
			$result[]=$plan->to_array();
		}
		

		
		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function test(){
		$policy = \App\Models\Policy::find([10267]);
		print_r($policy->getPaymentDates());
		die();
	}


/*

	public function test(){
		$policy = \App\Models\Policy::find([10267]);
		print_r($policy->financed());
	}
=======
		
		$payments = \App\Models\Payment::find_by_sql('SELECT * FROM respaldo_cobranzas.payments');
		$errors =0;
		$created=0;
		foreach($payments as $payment){
			$policy = \App\Models\Policy::find_by_sql(
				"SELECT id FROM policies WHERE client_id = (
				SELECT id FROM clients WHERE h_id = (
				SELECT h_id FROM respaldo_cobranzas.clients WHERE id = (
				SELECT client_id FROM respaldo_cobranzas.payments WHERE id =$payment->id)
				)
				)
				");
			if(count($policy)<=0){
				$errors=$errors+1;
			}
			
		if($errors ===0){
			$policy[0]->create_payment([
				'office'=>$payment->city,
				'payment_method'=>$payment->payment_method,
				'payment_type'=>$payment->payment_type,
				'payment_date'=>$payment->payment_date,
				'agency_discount'=>$payment->agency_discount,
				'agent_discount'=>$payment->agent_discount,
				'company_discount'=>$payment->company_discount,
				'comment'=>$payment->comment,
				'currency'=>$payment->currency,
				'processed'=>$payment->processed,
				'account_id'=>$payment->account_id,
				'amount'=>$payment->amount,
				'user_id'=>$payment->user_id,
				'change_rate'=>$payment->change_rate
			]);
			$created= $created+1;
		}
		
			
>>>>>>> Stashed changes

		}
		
	}
*/
	public function reportCorrection(){
		switch($this->payload['type']){
			case 'expenses':
			$expense = \App\Models\Expense::find([$this->payload['ref']]);
			$income = new \App\Models\Income(['date'=>date('Y-m-d H:i:s'),'account_id'=>$expense->account_id,'category_id'=>98,'user_id'=>$this->current_id,'description'=>"Correccion de gasto #".$expense->id,'currency'=>$expense->currency,'amount'=>$expense->amount]);
			$income->account->deposit($income->amount,$income->currency);
			$income->save();
			$expense->corrected_with = $income->id;
			$expense->save();
			break;
			case 'incomes':
			$income = \App\Models\Income::find([$this->payload['ref']]);
			$expense = new \App\Models\Expense(['date'=>date('Y-m-d H:i:s'),'account_id'=>$income->account_id,'category_id'=>97,'user_id'=>$this->current_id,'description'=>"Correccion de Ingreso #".$income->id,'currency'=>$income->currency,'amount'=>$income->amount,'office'=>'sc','bill_number'=>'S/N']);
			$expense->save();
			$expense->account->withdraw($expense->currenty,$expense->account);
			$income->corrected_with = $expense->id;
			$income->save();
			break;
			case 'payments':
			$payment = \App\Models\Payment::find([$this->payload['ref']]);
			$payment->revert($this->current_id);
			break;
			default:

			break;

		}
		$this->response(['errors'=>false,'data'=>'Correccion Realizada!']);
	}

	

}


?>