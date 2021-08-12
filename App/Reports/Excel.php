<?php
namespace App\Reports;

use \PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel
{
    private $folder = "../App/Files/";
    protected $file;
    protected $sheet = null;
    private $row=1;
    private $col=1;
    private $cell;

    public function __set($k, $v) {
        $this->$k = $v;

        if ($k==='col' || $k==='row') {
            $this->cell = $this->sheet->getCellByColumnAndRow($this->col, $this->row);
        }
    }

    public function __get($k) {
        return $this->$k;
    }

    public function nextRow() {
        return $this->row+1;
    }

    public function prevRow() {
        return $this->row-1;
    }

    public function nextCol() {
        return $this->col +1;
    }

    public function prevCol() {
        return $this->col - 1;
    }

    public function __construct($file) {
        $this->file = IOFactory::load($this->folder.$file);
        $this->sheet=$this->file->getActiveSheet();
        $this->cell = $this->sheet->getCellByColumnAndRow(1, 1);
    }

    public function getCell() {
        return $this->cell;
    }

    protected function center($range) {
        $sheet = $this->sheet;
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    protected function merge($range) {
        $sheet = $this->sheet;
        $sheet->mergeCells($range);
    }

    protected function mergeAndCenter($range) {
        $this->merge($range);
        $this->center($range);
    }

    public function setValue($cell, $value) {
        $sheet = $this->sheet;
        $sheet->setCellValue($cell, $value);
    }

    protected function formatToNumber($range) {
        $sheet = $this->sheet;
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("J1:J999")->getNumberFormat()->setFormatCode('0.00');
    }

    public function download($filename) {
        $writer = new Xlsx($this->file);
        header('Content-type:application/vnd.ms-excel');
        header("Content-disposition:attachment;filename=$filename".'.xlsx');
        $writer->save('php://output');
    }

    public function base64($filename) {
        ob_start();
        $writer = new Xlsx($this->file);
        $writer->save('php://output');
        $data =ob_get_clean();
    
        return base64_encode($data);
    }
}
