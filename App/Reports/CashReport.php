<?php
namespace App\Reports;

class CashReport extends Excel
{
    private $companyRange = "A1:J1";
    private $titleRange = "A2:J2";
    private $officeRange = "A3:J3";
    private $dateRange = "A4:J4";
    private $dataStartRow = 7;
    private $saldoInicial = 0;

    private $debe= ['USD'=>0,"BOB"=>0];
    private $haber = ['USD'=>0,"BOB"=>0];

    public function __construct($title) {
        parent::__construct('caja.xlsx');
    }

    public function format() {
        $sheet = $this->sheet;
        $this->setValue("A1", "Power Selling S.R.L");
        $this->mergeAndCenter($this->companyRange);
        $this->mergeAndCenter($this->titleRange);
        $this->mergeAndCenter($this->officeRange);
        $this->mergeAndCenter($this->dateRange);
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getStyle("C1:C999")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
    }

    public function writeData(array $data, array $saldos) {
        $sheet = $this->sheet;
        $this->setSaldoInicial($saldos);
        $this->row=$this->dataStartRow;
        
        foreach ($data as $fields) {
            $row = $this->row;
            $prevRow = $this->prevRow();
            $this->col = 1;
            $this->debe[$fields['currency']]+=$fields['debe'];
            $this->haber[$fields['currency']]+=$fields['haber'];
            foreach ($fields as $name=>$value) {
                $this->cell->setValue($value);
                $this->col++;
            }

            $condition = 'E'.$this->row.'="BOB"';
            $formulaValue = "=IF($condition,H$prevRow,H$prevRow+F$row-G$row)";
            $this->cell->setValue($formulaValue);
            $condition = 'E'.$this->row.'="USD"';
            $this->col++;
            $formulaValue = "=IF($condition,I$prevRow,I$prevRow+F$row-G$row)";

            $this->cell->setValue($formulaValue);
            $this->col++;
            $this->cell->setValue("=H$row+(I$row/6.96)");
            $this->row++;
        }
        
        $this->setTotales($this->row);
        $this->formatToNumber("A1:Z99");
    }

    public function addTotal($row) {
    }

    public function setTotales($lastRow) {
        $totalRow = $lastRow+2;
        $styles = $this->sheet->getStyle("A$totalRow:C$totalRow");
        $styles
        ->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('000000');
        $styles->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $this->setValue("A$totalRow", "TOTALES");
        $this->setValue("B$totalRow", "USD");
        $this->setValue("C$totalRow", "BOB");
        $totalRow++;
        $this->setValue("A$totalRow", "Saldo Inicial");
        $this->setValue("B$totalRow", $this->saldos['USD']);
        $this->setValue("C$totalRow", $this->saldos['BOB']);
        $totalRow++;
        $this->setValue("A$totalRow", "Total Ingreso");
        $this->setValue("B$totalRow", $this->debe['USD']);
        $this->setValue("C$totalRow", $this->debe['BOB']);
        $totalRow++;
        $this->setValue("A$totalRow", "Total Egreso");
        $this->setValue("B$totalRow", $this->haber['USD']);
        $this->setValue("C$totalRow", $this->haber['BOB']);
        $totalRow++;
        $styles = $this->sheet->getStyle("A$totalRow:C$totalRow");
        $styles
        ->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('000000');
        $styles->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        
        $this->setValue("A$totalRow", "Saldo Final");
        $this->setValue("B$totalRow", $this->saldos['USD']+$this->debe['USD']-$this->haber['USD']);
        $this->setValue("C$totalRow", $this->saldos['BOB']+$this->debe['BOB']-$this->haber['BOB']);
    }

    public function setSaldoInicial($saldos) {
        $this->mergeAndCenter("A6:G6");
        $this->saldos = $saldos;
        $this->setValue("A6", "SALDOS INICIALES");
        $this->setValue("H6", $this->saldos['USD']);
        $this->setValue("I6", $this->saldos['BOB']);
        $this->setValue("J6", $this->saldos['USD']+($this->saldos['BOB']*6.96));
    }
}
