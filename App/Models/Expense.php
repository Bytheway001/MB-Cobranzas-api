<?php

namespace App\Models;

class Expense extends \ActiveRecord\Model {
    public static $belongs_to = [
        ['account'],
        ['category'],
        ['user'],
    ];

    public function revert($user_id) {
        try {
            $income = new Income([
                'date'       => date('Y-m-d H:i:s'),
                'account_id' => $this->account_id,
                'category_id'=> 98, 'user_id'=>$user_id,
                'description'=> 'Correccion de gasto #'.$this->id,
                'currency'   => $this->currency,
                'amount'     => $this->amount,
            ]);
            if ($income->save()) {
                $income->account->deposit($income->amount, $income->currency);
                $this->corrected_with = $income->id;
                $this->save();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
