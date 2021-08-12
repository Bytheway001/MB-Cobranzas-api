<?php

namespace App\Models;

class Agent extends Model
{
    public static $has_many = [['clients']];
}
