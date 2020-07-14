<?php 
namespace App\Models;

class Payment extends \ActiveRecord\Model{
	static $belongs_to=[['client'],['user'],['account']];

	public function serialize(){
		$payment = $this->to_array();
		$payment['payment_date']=$this->payment_date->format('d-m-Y');
		$payment['client']=$this->client->first_name;
		$payment['collector']=$this->client->collector->name;
		$payment['plan']=$this->client->plan;
		$payment['company']=$this->client->company;
		$payment['account_name']=$this->account?$this->account->name:'--';
		$payment['payment_method']=$this->serializePaymentMethods($this->payment_method);
		return $payment;
	}

	private function serializePaymentMethods($method){
		$methods=[
			'cash_to_agency'=>'Efectivo la agencia',
			'check_to_agency_local'=>'Cheque local a la agencia',
			'check_to_agency_foreign'=>'Cheque extranjero a la agencia',
			'transfer_to_agency_foreign'=>'Transferencia a la agencia (Exterior)',
			'transfer_to_agency_local'=>'Transferencia la agencia (Local)',
			'claim_to_company'=>'Abono de reclamo',
			'tdc_to_collector'=>'Tarjeta de Credito A la Aseguradora',
			'check_to_foreign_company'=>'Cheque extranjero a la Aseguradora',
			'transfer_to_company'=>'Transferencia Bancaria a la Aseguradora',
			'tdc_to_company'=>'Pago en portal de cliente(TDC)'
		];
		return $methods[$method];
	}
}

 ?>