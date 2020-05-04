<?php 
namespace App\Models;

class Payment extends \ActiveRecord\Model{
	static $belongs_to=['client'];

	public function serialize(){
		$result=[];
		$result['date']=date('d/m/Y',strtotime($this->payment_date));
		$result['client']=$this->client->name;
		$result['collector']=$this->client->collector->name;
		$result['payment_method']=$this->serializePaymentMethods($this->payment_method);
		if($this->payment_method !== 'transfer_to_foreign_agency' && $this->payment_method!=='transfer_to_local_agency'){
			if($this->payment_method == 'cash_to_agency'){
				$result['account']='Caja';
			}
			else{
				$result['account']='--';
			}
			
		}
		else{
			$result['account']="Cuenta".$this->account;
		}
		$result['amount']=$this->amount;

		return $result;
	}

	private function serializePaymentMethods($method){
		$methods=[
			'cash_to_agency'=>'Efectivo la agencia',
			'check_to_local_agency'=>'Cheque local a la agencia',
			'check_to_foreign_agency'=>'Cheque extranjero a la agencia',
			'transfer_to_foreign_agency'=>'Transferencia bancaria a cuenta extranjera a la agencia',
			'transfer_to_local_agency'=>'Transferencia bancaria a cuenta local de agencia',
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