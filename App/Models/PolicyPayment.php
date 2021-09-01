<?php

namespace App\Models;

class PolicyPayment extends \ActiveRecord\Model
{
    /**
     * Validations
     */
    public static $validates_presence_of =[
        ['policy_id'],
        ['amount'],
        ['currency'],
        ['payment_type'],
        ['payment_date'],

    ];

    public static $belongs_to = [['account'], ['policy'], ['user']];
    public static $before_create = ['set_creator'];

    public function validate() {
        /* Account should have the amount for this operations */
        if ($this->account) {
            if (!$this->account->has($this->amount, $this->currency)) {
                $this->errors->add('amount', "is not enough");
            }
        }
    }

    public function set_creator() {
        $request = \Core\Request::instance();
        $this->user_id = $request->user->id;
    }

    public function revert($user_id) {
        try {
            $income = new Income([
                'date'       => date('Y-m-d H:i:s'),
                'account_id' => $this->account_id,
                'category_id'=> 98, 'user_id'=>$user_id,
                'description'=> 'Correccion Pago Polizas #'.$this->id,
                'currency'   => $this->currency,
                'amount'     => $this->amount,
            ]);
            if ($income->save()) {
                $this->corrected_with = $income->id;
                $this->save();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
