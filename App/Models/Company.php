<?php

namespace App\Models;

class Company extends \ActiveRecord\Model {
    public static $has_many = [
        ['plans'],
    ];
}
