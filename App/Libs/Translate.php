<?php

namespace App\Libs;

class Translate {
    public static $office_names = ['sc'=>'Santa Cruz', 'lp'=>'La Paz', 'cb'=>'Cochabamba', 'ss'=>'Sistema'];

    public static function officeName($officeName) {
        return static::$office_names[$officeName];
    }
}
