<?php

namespace App\Libs;

class Translate
{
    public static $office_names = ['sc'=>'Santa Cruz', 'lp'=>'La Paz', 'cb'=>'Cochabamba', 'ss'=>'Sistema'];

    public static function officeName($officeName) {
        return static::$office_names[$officeName];
    }

    public static function monthNames($month) {
        $months=[null,'Ene',"Feb","Mar",'Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        return $months[$month];
    }
}
