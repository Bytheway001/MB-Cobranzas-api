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
			$collector=User::find_by_name($client['collector']);
			if(!$collector){
				$collector=User::create(['name'=>$client['collector']]);
			}

			$client['agent_id']=$agent->id;
			$client['collector_id']=$collector->id;

			$client['effective_date']=$this->setDateFormat($client['effective_date'],'Y-m-d');
			$client['renovation_date']=$this->setDateFormat($client['renovation_date'],'Y-m-d');
			unset($client['agent']);
			unset($client['collector']);

			Client::create($client);

		}
		$this->response(['errors'=>false,'data'=>"Clientes creados con exito"]);

		
		
		

	}

	public function index(){
		$result=[];
		try{
			if(isset($_GET['criteria'])){
				if($_GET['criteria'] == 'client'){
					$clients=Client::all(['conditions'=>["name LIKE '%".$_GET['term']."%'"]]);
				}
				else{
					$clients=Client::all(['conditions'=>['policy_number = ?',$_GET['term']]]);
				}
			}
			else{
				$clients=Client::all();
			}
			
			
			
			foreach($clients as $client){
				$data=$client->serialize();
				
				$result[]=$data;
			}

			$this->response(['errors'=>false,'data'=>$result]);
		}
		catch(\Exception $e){
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
			$result['payments'][]=$payment->to_array();
		}
		$this->response(['errors'=>false,'data'=>$result]);
	}

	private function setDateFormat($date,$format){

		$newDate = date($format, strtotime($date));  
		return $newDate;  
	}


}


?>