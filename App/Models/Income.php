<?php

namespace App\Models;

class Income extends \ActiveRecord\Model
{
    public static $belongs_to = [['account'], ['user'], 'category'];

    public function serialize()
    {
        $result = $this->to_array();
        $result['account'] = $this->account->name;
        $result['date'] = $this->date->format('d-m-Y');
        $result['user'] = $this->user ? $this->user->name : 'Ninguno';

        return $result;
    }

    public function revert($user_id)
    {
        try {
            $expense = new Expense([
                'date'       => date('Y-m-d H:i:s'),
                'account_id' => $this->account_id,
                'category_id'=> 97,
                'user_id'    => $user_id,
                'description'=> 'Correccion de Ingreso #'.$this->id,
                'currency'   => $this->currency,
                'amount'     => $this->amount,
                'office'     => 'sc',
                'bill_number'=> 'S/N',
            ]);
            if ($expense->save()) {
                $expense->account->withdraw($expense->amount, $expense->currency);
                $this->corrected_with = $expense->id;
                $this->save();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
