<?php

namespace App\Models;

class Agent extends \ActiveRecord\Model
{
    public static $has_many = [['clients']];
}
