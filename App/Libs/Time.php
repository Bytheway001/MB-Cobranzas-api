<?php 

namespace App\Libs;

class Time{
	static function convert($date,$inputFormat,$outputFormat){
		$date = \DateTime::createFromFormat($inputFormat,$date);
		return $date->format($outputFormat);  
	}

	static function getasDate($inputFormat,$date){
			$date = \DateTime::createFromFormat($inputFormat,$date);
			return $date;
	}

	static function addDays($date,$days){
		return $date->add(new \DateInterval('P'.$days.'D'));
	}

}

?>