<?php 

namespace App\Controllers;
class Controller{
	public function __construct(){
		$this->payload = json_decode(file_get_contents("php://input"), TRUE);
	//	$this->authenticateRequest();
		
	}
	protected function response(array $response){
		header('Content-Type:application/json');
		echo json_encode($response);
		die(); 
	}

	private function authenticateRequest(){
		$uri = strtok($_SERVER["REQUEST_URI"], '?');
		if($uri !=='/auth'){
			if(!isset($_SERVER['HTTP_U'])){
				http_response_code(403);
				$this->response(['errors'=>true,'data'=>'NOT AUTHENTICATED']);
			}
			else{
				$this->current_id = $_SERVER['HTTP_U'];
			}
		}

	}

	
}

?>