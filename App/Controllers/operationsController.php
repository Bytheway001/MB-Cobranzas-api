<?php 
namespace App\Controllers;
use \App\Models\Transfer;
use \App\Models\Account;
class operationsController extends Controller{
	public function createTransfer(){
		$transfer = new Transfer($this->payload);
		$fromAccount = Account::find([$this->payload['from']])->to_array();
		if($fromAccount[strtolower($this->payload['currency'])] < $this->payload['amount']){
			http_response_code(401);
			$this->response(['errors'=>true,'data'=>"Saldo insuficiente"]);
		}
		if($transfer->save()){
			$this->response(['errors'=>false,'data'=>"Transferencia realizada con exito"]);
		}
	}

	public function collect_check(){
		$check = \App\Models\Check::find([$this->payload['checkId']]);
		$account = Account::find([$this->payload['accountId']]);
		$check->status = 'Abonado en cuenta';
		$check->account_id = $account->id;
		$check->save();
		if($check->currency==='USD'){
			$account->usd = $account->usd+$check->amount;
		}
		else{
			$account->bob = $account->bob+$check->amount;
		}
		
		$account->save();
		$this->response(['errors'=>false,'data'=>"Cheque abonado a cuenta"]);
	}
}

?>