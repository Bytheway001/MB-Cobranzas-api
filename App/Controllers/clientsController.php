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
		foreach($this->payload as $client){
			$agent=Agent::find_by_name($client['agent']);
			if(!$agent){
				$agent=Agent::create(['name'=>$client['agent']]);
			}
			if(isset($client['collector'])){
				$collector=User::find_by_name($client['collector']);
				if(!$collector){
					$collector=User::create(['name'=>$client['collector']]);
				}
				$client['collector_id']=$collector->id;
				
			}
			else{
				$client['collector_id']=null;
			}
			$client['agent_id']=$agent->id;
			
			$client['effective_date']=$this->setDateFormat($client['effective_date'],'Y-m-d');
			$client['renovation_date']=$this->setDateFormat($client['renovation_date'],'Y-m-d');
			$client['first_name']=$client['name'];
			unset($client['name']);
			unset($client['agent']);
			unset($client['collector']);

			Client::create($client);

		}
		$this->response(['errors'=>false,'data'=>"Clientes creados con exito"]);

		
		
		

	}

	public function index(){
		try{
			$criteria = isset($_GET['criteria'])?$_GET['criteria']:null;
			$term = isset($_GET['term'])?$_GET['term']:null;
		

			$clients=[];
			$result=[];
			if($criteria){
				if($criteria=='client'){
					$clients=Client::all(['conditions'=>["first_name LIKE ?",'%'.$term.'%']]);
				}
				else{
					$clients=Client::all(['conditions'=>['policy_number = ?',$term]]);
				}
			}
			else{
				$clients=Client::all();
			}
			foreach($clients as $client){
				$result[]=$client->serialize();
			}
			$this->response(['errors'=>false,'data'=>$result]);

		}

		catch(\ActiveRecord\DatabaseException $e){
			echo Client::table()->conn->last_query;
			echo $e->getMessage();
		}

	}





	public function updatePolicy($id){
		$client=Client::find_by_id($id);
		if($client){
			if($client->update_attributes($this->payload)){

				$this->response(['errors'=>false,'data'=>$client->serialize()]);
			}
			else{
				$this->response(['errors'=>true,'data'=>"UPDATE_IMPOSSIBLE"]);
			}
		}
		else{
			http_response_code(400);
			$this->response(['errors'=>true,'data'=>"CLIENT_NOT_FOUND"]);
		}
	}

	public function show($id){

		$client=Client::find_by_id($id);

		$result=$client->serialize();
		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function profile($id){
		$client=Client::find_by_id($id);
		$result=$client->serialize();
		$result['payments']=[];
		foreach($client->payments as $payment){
			$p=$payment->to_array();
			$p['date']=$payment->payment_date->format('d-m-Y');
			$p['user']=$payment->user->name;
			$result['payments'][]=$p;
		}
		$result['policy_payments']=[];
		foreach($client->policy_payments as $p){
			$pp=$p->to_array();
			$pp['date']=$p->created_at->format('d-m-y');
			$pp['user']=$p->user->name;
			$result['policy_payments'][]=$pp;
		}
		$this->response(['errors'=>false,'data'=>$result]);
	}

	private function setDateFormat($date,$format){
		

		$newDate = date($format, strtotime($date));  
		return $newDate;  
	}

}



?>