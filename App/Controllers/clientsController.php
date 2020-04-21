<?php 
namespace App\Controllers;
use \App\Models\Client;
use \App\Models\Agent;
use \App\Models\User;
class clientsController extends Controller{
	public function create(){
		$client=new Client($this->payload);
		$client->save();
		$this->response(['errors'=>false,'data'=>$client->to_array()]);
	}

	public function bulk(){
		try{
			
			foreach($this->payload as $client){
				$agent=Agent::find_by_name($client['agent']);
				$collector=User::find_by_name($client['collector']);
				$client['agent_id']=$agent->id;
				$client['collector_id']=$collector->id;
				$client['effective_date']=$this->setDateFormat($client['effective_date']);
				$client['renovation_date']=$this->setDateFormat($client['renovation_date']);
				unset($client['agent']);
				unset($client['collector']);
			
				Client::create($client);
				$this->response(['errors'=>false,'data'=>"Clientes creados con exito"]);
			}
			
		}
		catch(\Exception $e){
			print($e->getMessage());
		}
		

	}

	private function setDateFormat($date){

		$newDate = date("Y-m-d", strtotime($date));  
		return $newDate;  
	}
}


?>