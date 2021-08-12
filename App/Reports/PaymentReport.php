<?php
namespace App\Reports;

class PaymentReport extends Excel
{
    public function __construct() {
        parent::__construct('cobranzas.xlsx');
        $this->format();
    }

    public function format() {
        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setAutoSize(true);
    }

    public function writeData($data) {
        $this->sheet->fromArray($data, null, "A5", true);
    }
}
