<?php

namespace App\Models;

class User extends \ActiveRecord\Model
{
    public static $belongs_to = [['account']];
}
