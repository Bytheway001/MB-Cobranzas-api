<?php

namespace App\Models;

class Expense extends \ActiveRecord\Model
{
    public static $before_create = ['withdraw_from_account'];

    public static $belongs_to = [
        ['account'],
        ['category'],
        ['user'],
    ];

    public function withdraw_from_account() {
        $this->user_id = \Core\Request::instance()->user->id;
        if ($this->account_id) {
            if ($this->account->has($this->amount, $this->currency)) {
                $this->account->withdraw($this->amount, $this->currency);
            } else {
                $this->errors->add('Monto', 'no disponible en cuenta');
                return false;
            }
        }
    }
}
