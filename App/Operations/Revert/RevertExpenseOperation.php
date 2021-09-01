<?php
namespace App\Operations\Revert;

use App\Operations\Operation;
use App\Operations\IOperation;
use Core\ApiException;

class RevertExpenseOperation extends Operation implements IOperation
{
    public function validateRequest() {
    }

    public function __construct() {
        parent::__construct();
        $this->ref = $this->payload['ref'];
    }

    public function process() {
        $this->setExpense();
        $this->revertExpense();
        $this->prepareResponse();
    }

    public function setExpense() {
        try {
            $this->expense = \App\Models\Expense::find([$this->ref]);
            if ($this->expense->corrected_with !== null) {
                $this->errors[] = 'Este gasto ya fue corregido';
                $this->fail = 'Expense already reverted';
            }
        } catch (\ActiveRecord\RecordNotFound $e) {
            $this->errors[] = "Este gasto no existe";
            $this->fail("Expense not found");
        }
    }

    public function revertExpense() {
        $this->income = $this->expense->account->build_income([
            'category_id'=>96,
            'user_id'=>\Core\Request::instance()->user->id,
            'date'=>date('Y-m-d H:i:s'),
            'description'=>"Correccion de gasto #".$this->expense->id,
            'currency'=>$this->expense->currency,
            'amount'=>$this->expense->amount,
            'correcting'=>$this->expense->id,
            'correcting_type'=>'expense'
        ]);
        $this->income->save();

        $this->expense->update_attributes(['corrected_with'=> $this->income->id]);
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
