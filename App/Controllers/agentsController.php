<?php 
namespace App\Controllers;
use \Core\View;
use \App\Models\Agent;
use \App\Models\User;
use \App\Models\Payment;
class agentsController extends Controller{
	public function index(){
		$result = [];
		$agents = Agent::all();
		foreach($agents as $agent){
			$result[] = $agent->to_array();
		}
		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function getCollectors(){
		$result=[];
		$collectors = User::all(['conditions'=>['role = ?','collector']]);
		foreach($collectors as $collector){
			$result[]=$collector->to_array();
		} 

		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function createPayment(){
		$payment=new Payment($this->payload);
		if($payment->save()){
			$this->response(['errors'=>false,'data'=>"Cobranza Registrada exitosamente"]);
		}
		else{
			$this->response(['errors'=>true,'data'=>"No se pudo registrar la cobranza"]);
		}

	}
}


 ?>