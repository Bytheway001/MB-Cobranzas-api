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
		$checks =\App\Models\Check::all();
		foreach($checks as $check){
			$arr= $check->to_array();
			$arr['client']=$check->client->first_name;
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
			\App\Models\Movement::create(['date'=>date('Y-m-d'),'type'=>"OUT",'description'=>"Cambio de Moneda",'amount'=>$amount,'currency'=>$coinFrom,'origin'=>$accountFrom->id]);
			\App\Models\Movement::create(['date'=>date('Y-m-d'),'type'=>"IN",'description'=>"Cambio de Moneda",'amount'=>$converted,'currency'=>$coinTo,'destiny'=>$accountTo->id]);
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
			$result[]=$company->to_array();
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
		$m= new \App\Models\MovementNew(['from'=>5,'to'=>1,'description'=>'Test','currency'=>'USD','amount'=>'100']);
		if($m->process()){
			$this->response(['errors'=>false,'data'=>'created']);
		}
		else{
			$this->response(['errors'=>false,'data'=>'error']);
		}
	}

	public function reportCorrection(){
		$this->payload['user_id']=$this->current_id;
		$correction = new \App\Models\Change($this->payload);
		if($correction->save()){
			$this->response(['errors'=>false,'data'=>'Reportado!']);
		}
		else{
			$this->response(['errors'=>true,'data'=>"No se pudo reportar"]);
		}
	}

	

}


?>