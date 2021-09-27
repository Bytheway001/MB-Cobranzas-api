<?php
namespace App\Models;

class Renewal extends Model
{
    public static $belongs_to = [['policy'],['plan']];

    public function get_period() {
        return ($this->renovation_date->format('Y')-1).'-'.$this->renovation_date->format('Y');
    }
}
