<?php 

namespace App\Libs;

class Translate{
	static $office_names = ['sc'=>"Santa Cruz",'lp'=>'La Paz','cb'=>'Cochabamba',"ss"=>"Sistema"];

	static function officeName($officeName){
		return static::$office_names[$officeName];
	}
}


 ?>