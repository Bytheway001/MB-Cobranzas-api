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
		if(!$this->payload['account_id']){
			$this->payload['account_id'] = null;
		}

		$payment=new Payment($this->payload);
		$payment->user_id = $this->current_id;
		$payment->payment_date =$this->setDateFormat($this->payload['payment_date'],'Y-m-d');
		if(!$payment->client->isLinkedToHubSpot()){
			$payment->client->linkToHubSpot();
		}


		switch($payment->payment_method){
			case 'check_to_agency_local':
			$check = new \App\Models\Check(['amount'=>$payment->amount,'currency'=>$payment->currency,'client_id'=>$payment->client_id]);
			$check->save();
			break;
			case 'check_to_agency_foreign':
			$check = new \App\Models\Check(['amount'=>$payment->amount,'currency'=>$payment->currency,'client_id'=>$payment->client_id]);
			$check->save();
			break;
			default:
			break;

		}
		if($payment->save()){
			$data='Cobranza efectuada en sistema por un monto de '.$payment->currency.' '.$payment->amount;
			if($payment->client->isLinkedToHubSpot()){
				//$payment->client->addHubSpotNote('Cobranza creada con exito',$data);
			}

			$this->response(['errors'=>false,'data'=>"Cobranza Registrada exitosamente"]);
		}
		else{
			$this->response(['errors'=>true,'data'=>"No se pudo registrar la cobranza"]);
		}
	}
	private function setDateFormat($date,$format){
		$newDate = date($format, strtotime($date));  
		return $newDate;  
	}
	
}


?>