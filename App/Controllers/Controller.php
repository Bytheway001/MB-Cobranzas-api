<?php 
/**
* This is the main controller class, The controllers should inherit from this class (for custom views) or from \Core\Crud (for simple crud scaffolds)
*/
namespace App\Controllers;
class Controller{
	public function __construct(){
		$this->payload = json_decode(file_get_contents("php://input"), TRUE);
		if(!$this->authenticateRequest()){
			$this->response(['errors'=>true,'data'=>"USUARIO NO AUTENTICADO"]);
		}
	}
	protected function response(array $response){
		header('Content-Type:application/json');
		echo json_encode($response);
		die(); 
	}

	private function authenticateRequest(){
		return true;
	}
}

?>