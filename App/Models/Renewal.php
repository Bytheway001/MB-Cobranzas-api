<?php
namespace App\Models;

class Renewal extends \ActiveRecord\Model {
    public static $belongs_to = [['policy']];
}
