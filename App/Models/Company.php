<?php

namespace App\Models;

class Company extends Model
{
    public static $has_many = [
        ['plans'],
        ['policies','through'=>'plans']
    ];
}
