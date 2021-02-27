<?php

namespace App\Models;

class Plan extends \ActiveRecord\Model {
    public static $belongs_to = [
        ['company'],
    ];

    public static $has_many = [
        ['policies']
    ];

    public function print_data() {
        print_r($this);
        exit();
    }
}
