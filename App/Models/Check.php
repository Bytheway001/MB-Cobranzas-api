<?php

namespace App\Models;

class Check extends \ActiveRecord\Model {
    public static $belongs_to = [['client'], ['account']];
}
