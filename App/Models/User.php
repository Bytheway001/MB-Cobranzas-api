<?php

namespace App\Models;

class User extends Model
{
    public static $belongs_to = [['account']];
}
