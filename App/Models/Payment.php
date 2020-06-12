<?php 
namespace App\Models;

class Payment extends \ActiveRecord\Model{
	static $belongs_to=[['client'],['user']];

	public function serialize(){
		$payment = $this->to_array();
		$payment['payment_date']=$this->payment_date->format('d-m-Y');
		$payment['client']=$this->client->first_name;
		$payment['collector']=$this->client->collector->name;
		return $payment;
	}

	private function serializePaymentMethods($method){
		$methods=[
			'cash_to_agency'=>'Efectivo la agencia',
			'check_to_agency_local'=>'Cheque local a la agencia',
			'check_to_agency_foreign'=>'Cheque extranjero a la agencia',
			'transfer_to_agency_foreign'=>'Transferencia bancaria a cuenta extranjera a la agencia',
			'transfer_to_agency_local'=>'Transferencia bancaria a cuenta local de agencia',
			'claim_to_company'=>'Abono de reclamo',
			'tdc_to_collector'=>'Tarjeta de Credito para que cobradora pague la poliza',
			'check_to_foreign_company'=>'Cheque extranjero a la Aseguradora',
			'transfer_to_company'=>'Transferencia Bancaria a la Aseguradora',
			'tdc_to_company'=>'Pago en portal de cliente(TDC)'
		];
		return $methods[$method];
	}
}

 ?>