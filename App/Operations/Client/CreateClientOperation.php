<?php
namespace App\Operations\Client;

use App\Operations\Operation;
use App\Operations\IOperation;
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;
use App\Models\Client;

class CreateClientOperation extends Operation implements IOperation
{
    public function __construct() {
        parent::__construct();
        $this->action = 'create_client';
    }

    public function process() {
        try {
            $this->validateRequest();
            $this->findDuplicates();
            $this->saveIntoDB();
            $this->prepareResponse();
        } catch (ApiException $e) {
            $this->response = $this->errors;
        }
    }

    public function validateRequest() {
        $params =  Yaml::parse(file_get_contents('../Config/params.yaml'));
        $action = $params[$this->action];
        if (!empty($action['payload'])) {
            foreach ($action['payload'] as $param) {
                if (!array_key_exists($param, $this->payload)) {
                    $this->errors['request']=$param.' is Missing';
                    $this->fail(400, 'Invalid Params');
                }
            }
        }
        if (count($this->errors)>0) {
            $this->statusCode = 403;
            throw new ApiException("Bad Request");
        }
    }

    public function findDuplicates() {
        $clients_found = Client::count(['conditions'=>['h_id = ?',$this->payload['h_id']]]);
        if ($clients_found>0) {
            $this->errors[]='El cliente ya existe';
            $this->fail(400, "Client Exists");
        }
    }

    public function saveIntoDB() {
        $this->client = new Client($this->payload);
        $this->client->save();
    }

    public function prepareResponse() {
        if (count($this->errors)>0) {
            $this->connection->rollback();
            $this->response = $this->errors;
            return;
        }
        $this->connection->commit();
        $this->done = true;
        $this->statusCode = 201;
        $this->response=$this->client->serialized();
    }
}
