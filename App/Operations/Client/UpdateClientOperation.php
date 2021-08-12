<?php 
namespace App\Operations\Client;
use App\Operations\{Operation,IOperation};
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;
use App\Models\Client;
class UpdateClientOperation extends Operation implements IOperation{
	/**
	 *  1.- Validamos los parametros del request
	 *  2.- Validamos que no exista otro cliente con este id de hubspot
	 *  3.- Creamos el cliente
	 *  4.- Retornamos el cliente serializado
	*/
	public function __construct(){
		parent::__construct();
		$this->action = 'update_client';
	}
	
	public function process(){
		try{
			$this->validateRequest();
			$this->findClient();
			$this->updateClient();
			$this->prepareResponse();
		}
		catch(ApiException $e){
			$this->response = $this->errors;
		}
	}

	public function validateRequest(){
		if(empty($this->payload['id'])){
			$this->errors['client_id']="Must provide a client ID";
		}
		if(count($this->errors)>0){
			$this->statusCode = 403;
			throw new ApiException("Bad Request");
		}
	}

	public function findClient(){
		try{
			$this->client = Client::find([$this->payload['id']]);
		}

		catch(\ActiveRecord\RecordNotFound $e){
			$this->errors['client']="Client Not Found";
			$this->fail(404,'Client not Found');
		}
		

	}

	public function updateClient(){
		if(!$this->client->update_attributes($this->payload)){
			$this->fail("Could not update client");
		}
	}

	public function prepareResponse(){
		if(count($this->errors)>0){
			$this->connection->rollback();
			$this->response = $this->errors;
			return;
		}
		$this->connection->commit();
		$this->done = true;
		$this->statusCode = 200;
		$this->response=$this->client->serialized();
	}
}


?>