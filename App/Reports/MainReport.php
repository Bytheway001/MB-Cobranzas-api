<?php
namespace App\Reports;

class MainReport extends Excel
{
    public function __construct($name, $initialDate, $finalDate) {
        $this->initialDate=$initialDate;
        $this->finalDate=$finalDate;
        parent::__construct('main.xlsx');
    }

    public function format() {
        $this->selectSheet("Polizas Cobradas");
        $this->setValue('A2', 'Periodo del '.$this->initialDate->format('d-m-Y').' Al '.$this->finalDate->format('d-m-Y'));
        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setAutoSize(true);
        $this->sheet->getColumnDimension('D')->setAutoSize(true);
        $this->sheet->getColumnDimension('E')->setAutoSize(true);
        $this->sheet->getColumnDimension('F')->setAutoSize(true);
        $this->sheet->getColumnDimension('G')->setAutoSize(true);
        $this->selectSheet("Polizas Pagadas");
        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setAutoSize(true);
        $this->sheet->getColumnDimension('D')->setAutoSize(true);
        $this->sheet->getColumnDimension('E')->setAutoSize(true);
        $this->sheet->getColumnDimension('F')->setAutoSize(true);
        $this->sheet->getColumnDimension('G')->setAutoSize(true);
        $this->selectSheet("Polizas Pendientes de Pago");
        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setAutoSize(true);
        $this->sheet->getColumnDimension('D')->setAutoSize(true);
        $this->sheet->getColumnDimension('E')->setAutoSize(true);
        $this->selectSheet("Renovaciones");
        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setAutoSize(true);
        $this->sheet->getColumnDimension('D')->setAutoSize(true);
        $this->sheet->getColumnDimension('E')->setAutoSize(true);
        $this->sheet->getColumnDimension('F')->setAutoSize(true);
        $this->sheet->getColumnDimension('G')->setAutoSize(true);
    }

    public function selectSheet($name) {
        $this->file->setActiveSheetIndexByName($name);
        $this->sheet = $this->file->getActiveSheet();
    }

    public function writePolizasCobradas($payments) {
        $this->selectSheet('Polizas Cobradas');
        $lastRow = 5; /* Ultima fila donde esta el total */
        $this->sheet->insertNewRowBefore($lastRow, count($payments)-1);
        $this->sheet->fromArray($payments, null, "A4", true);
        $lastRowAfterData = $lastRow + count($payments) -1;
        $prevRowAfterData = $lastRowAfterData-1;
        $this->sheet->getCell("D$lastRowAfterData")->setValue("=SUM(D4:D$prevRowAfterData)");
    }

    public function writePolizasPagadas($payments) {
        $this->selectSheet('Polizas Pagadas');
        $lastRow = 5; /* Ultima fila donde esta el total */
        $this->sheet->insertNewRowBefore($lastRow, count($payments)-1);
        $this->sheet->fromArray($payments, null, "A4", true);
        $lastRowAfterData = $lastRow + count($payments) -1;
        $prevRowAfterData = $lastRowAfterData-1;
        $this->sheet->getCell("D$lastRowAfterData")->setValue("=SUM(D4:D$prevRowAfterData)");
    }

    public function writePendingPaymentPolicies($payments) {
        $this->selectSheet('Polizas Pendientes de Pago');
        $lastRow = 5; /* Ultima fila donde esta el total */
        $this->sheet->insertNewRowBefore($lastRow, count($payments)-1);
        $this->sheet->fromArray($payments, null, "A4", true);
        $lastRowAfterData = $lastRow + count($payments) -1;
        $prevRowAfterData = $lastRowAfterData-1;
        $this->sheet->getCell("D$lastRowAfterData")->setValue("=SUM(D4:D$prevRowAfterData)");
    }

    public function writeRenewals($renewals) {
        $this->selectSheet('Renovaciones');
        $this->sheet->fromArray($renewals, null, "A4", true);
    }

    public function writeData($data) {
        $payments = $data['payments'];
        $this->writePolizasCobradas($payments);
        $this->writePolizasPagadas($data['policy_payments']);
        $this->writePendingPaymentPolicies($data['pending_policies']);
        $this->writeRenewals($data['renewals']);
        $this->format();
    }
}
