<?php
namespace App\Operations;

use App\Operations\Operation;
use App\Operations\IOperation;
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;

class ConvertCurrencyOperation extends Operation implements IOperation
{
    public function __construct() {
        parent::__construct();
    }

    public function process() {
        try {
            $this->validateRequest();
            $this->setAccounts();
            $this->calculateAmounts();
            $this->validateAmount();
            $this->createConvertion();
            $this->prepareResponse();
        } catch (ApiException $e) {
            $this->response = $this->errors;
        }
    }

    public function validateRequest() {
    }

    public function setAccounts() {
        try {
            $this->giving_account = \App\Models\Account::find([$this->payload['from']]);
            $this->receiving_account = \App\Models\Account::find([$this->payload['to']]);
        } catch (\ActiveRecord\RecordNotFound $e) {
            $this->errors['account']="Account not found";
            $this->fail("Account not found");
        }
    }
    /* Amount available in the account should be higher or equal to the payload amount */
    public function validateAmount() {
        if (!$this->giving_account->has($this->payload['amount'], $this->origin_currency)) {
            $this->errors['amount']="Unavailable Amount";
            $this->fail(400, "Unavailable amount");
        }
    }

    public function calculateAmounts() {
        $convertion_type = explode('/', $this->payload['currency']);

        $this->origin_currency = $convertion_type[0];
        $this->destiny_currency = $convertion_type[1];
        if ($this->origin_currency === "USD") {
            $this->change_rate = $this->payload['rate'];
        } else {
            $this->change_rate = 1/$this->payload['rate'];
        }
    }

    public function createExpense() {
        $this->payload['user_id']=\Core\Request::instance()->user->id;
        $this->expense=$this->account->create_expense($this->payload);
    }

    public function createConvertion() {
        $user_id = \Core\Request::instance()->user->id;
        $this->giving_account->create_expense([
            'user_id'    => $user_id,
            'bill_number'=> 'S/N',
            'description'=> 'Cambio de divisas',
            'currency'   => $this->origin_currency,
            'amount'     => $this->payload['amount'],
            'category_id'=> 99,
            'date'       => date('Y-m-d H:i:s'),
        ]);

        $this->receiving_account->create_income([
            'user_id'    => $user_id,
            'description'=> 'Cambio de divisas',
            'category_id'=> 99,
            'currency'   => $this->destiny_currency,
            'amount'     => $this->payload['amount']*$this->change_rate,
            'account_id' => $this->payload['to'],
            'date'       => date('Y-m-d H:i:s'),
        ]);
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
        $this->response="Convertion was successful";
    }
}
