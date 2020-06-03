<?php 

namespace App\Libs;

class Time{
	static function format($date,$format){
		$newDate = date($format, strtotime($date));  
		return $newDate;  
	}
}

?>