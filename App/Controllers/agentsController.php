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
		$payment->payment_date =$this->setDateFormat($this->payload['payment_date'],'Y-m-d');
		if(!$payment->client->isLinkedToHubSpot()){
			$payment->client->linkToHubSpot();
		}

		
		if($payment->save()){
			$data='Cobranza efectuada en sistema por un monto de '.$payment->currency.' '.$payment->amount;
			if($payment->client->isLinkedToHubSpot()){
				$payment->client->addHubSpotNote('Cobranza creada con exito',$data);
			}

			$this->response(['errors'=>false,'data'=>"Cobranza Registrada exitosamente"]);
		}
		else{
			$this->response(['errors'=>true,'data'=>"No se pudo registrar la cobranza"]);
		}
		

	}

private function setDateFormat($date,$format){
		$date = str_replace('/', '-', $date);
		
		$newDate = date($format, strtotime($date));  
		return $newDate;  
	}
	
}


?>