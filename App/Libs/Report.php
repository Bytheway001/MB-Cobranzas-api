<?php 
namespace App\Libs;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Report{

	public function __construct(){
		$this->sheet = new Spreadsheet();
		$this->activeSheet = $this->sheet->getActiveSheet();
	}


	public function build($reportName){
		switch($reportName){
			case "cashmovements":
				
			break;
		}
	}



}

?>