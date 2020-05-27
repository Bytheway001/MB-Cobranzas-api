<?php 
namespace App\Controllers;
use \Core\View;
use \App\Models\User;
class homeController extends Controller{
	public function index(){
		$this->response(['errors'=>false,'data'=>'Welcome to MB-Cobranzas']);
	}
	public function listAccounts(){
		$result=[];
		$accounts = \App\Models\Account::all(['order'=>'name']);
		foreach($accounts as $account){
			$result[]=['id'=>$account->id,'name'=>$account->name,'usd'=>$account->usd,'bob'=>$account->bob];
		}

		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function listChecks(){
		$result=[];
		$checks =\App\Models\Check::all();
		foreach($checks as $check){
			$arr= $check->to_array();
			$arr['client']=$check->client->name;
			$result[] =$arr;

		}
		$this->response(['errors'=>false,'data'=>$result]);
	}

}


 ?>