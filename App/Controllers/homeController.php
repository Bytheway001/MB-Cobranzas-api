<?php 
namespace App\Controllers;
use \Core\View;
use \App\Models\User;
use \App\Models\Account;
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
		$this->response(['errors'=>false,'data'=>$user->to_array()]);
	}

	public function convert(){
		$currencies = explode('/',$this->payload['type']);
		$coinFrom = strtolower($currencies[0]);
		$coinTo=  strtolower($currencies[1]);
		$amount = $this->payload['amount'];
		$accountFrom = Account::find([$this->payload['from']]);
		if($coinFrom==='usd'){
			$converted = $amount * $this->payload['rate'];
		}
		else{
			$converted = $amount / $this->payload['rate'];
		}

		if($accountFrom->$coinFrom>=$amount){
			/* La cuenta tiene saldo */

			$accountTo = Account::find([$this->payload['to']]);
			$accountFrom->$coinFrom =$accountFrom->$coinFrom - $amount;
			$accountTo->$coinTo =$accountTo->$coinTo + $converted;
			$accountFrom->save();
			$accountTo->save();
			$this->response(['errors'=>false,'data'=>'Conversion Exitosa']);
		}
		else{
			http_response_code(401);
			$this->response(['errors'=>true,'data'=>'Saldo insuficiente en cuenta saliente']);
		}


		

	}

	

}


 ?>