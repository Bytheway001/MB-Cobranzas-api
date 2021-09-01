<?php

namespace App\Models;

class Income extends \ActiveRecord\Model
{
    public static $belongs_to = [['account'], ['user'], 'category'];
    public static $before_create = ['deposit_into_account'];

    public function deposit_into_account() {
        $this->user_id = \Core\Request::instance()->user->id;
        if ($this->account_id) {
            $this->account->deposit($this->amount, $this->currency);
        }
    }
    public function serialize() {
        $result = $this->to_array();
        $result['account'] = $this->account->name;
        $result['date'] = $this->date->format('d-m-Y');
        $result['user'] = $this->user ? $this->user->name : 'Ninguno';

        return $result;
    }
}
