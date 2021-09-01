<?php
namespace App\Operations\Revert;

use App\Operations\Operation;
use App\Operations\IOperation;
use Core\ApiException;

class RevertIncomeOperation extends Operation implements IOperation
{
    public function validateRequest() {
    }

    public function __construct() {
        parent::__construct();
        $this->ref = $this->payload['ref'];
    }

    public function process() {
        $this->setIncome();
        $this->revertIncome();
        $this->prepareResponse();
    }

    public function setIncome() {
        try {
            $this->income = \App\Models\Income::find([$this->ref]);
            if ($this->income->corrected_with !== null) {
                $this->errors[] = 'Este ingreso ya fue corregido';
                $this->fail = 'Income already reverted';
            }
        } catch (\ActiveRecord\RecordNotFound $e) {
            $this->errors[] = "Este ingreso no existe";
            $this->fail("Income not found");
        }
    }

    public function revertIncome() {
        $this->expense = $this->income->account->build_expense([
            'category_id'=>96,
            'user_id'=>\Core\Request::instance()->user->id,
            'date'=>date('Y-m-d H:i:s'),
            'description'=>"Correccion de ingreso #".$this->income->id,
            'currency'=>$this->income->currency,
            'amount'=>$this->income->amount,
            'correcting'=>$this->income->id,
            'correcting_type'=>'income'
        ]);
        $this->expense->save();
        $this->income->update_attributes(['corrected_with'=> $this->expense->id]);
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
        $this->response="Correccion Realizada";
    }
}
