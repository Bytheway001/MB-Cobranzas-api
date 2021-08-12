<?php

namespace App\Models;

class Notification extends Model
{
    public static $has_many = [
        ['plans'],
        ['policies','through'=>'plans']
    ];
}
