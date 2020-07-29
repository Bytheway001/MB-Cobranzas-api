<?php 
namespace App\Controllers;
use \App\Models\Transfer;
use \App\Models\Account;
use \App\Models\Movement;

class operationsController extends Controller{
	public function createTransfer(){
		$transfer = new Transfer($this->payload);
		if(!$transfer->origin->has($transfer->amount,$transfer->currency)){
			http_response_code(401);
			$this->response(['errors'=>true,'data'=>"Saldo insuficiente"]);
		}
		else{
			$transfer->origin->withdraw($transfer->amount,$transfer->currency);
			$transfer->destiny->deposit($transfer->amount,$transfer->currency);
			if($transfer->save()){
				if(Account::find([$transfer->from])->type==='Cash' ||  Account::find([$transfer->from])->to==='Cash'){
					Movement::create([
						'origin'=>$transfer->from,
						'type'=>"OUT",
						'description'=>'Transf. Interna',
						'amount'=>$transfer->amount,
						'currency'=>$transfer->currency,
						'date'=>date('Y-m-d')
					]);
					Movement::create([
						'destiny'=>$transfer->to,
						'type'=>"IN",
						'description'=>'Transf. Interna',
						'amount'=>$transfer->amount,
						'currency'=>$transfer->currency,
						'date'=>date('Y-m-d')
					]);
				}
				$this->response(['errors'=>false,'data'=>"Transferencia realizada con exito"]);
			}
			else{
				http_response_code(401);
				$this->response(['errors'=>true,'data'=>"No se pudo realizar la transferencia"]);
			}
		}
	}

	public function createIncome(){
		$income = new \App\Models\Income($this->payload);
		if($income->save()){
			$income->account->deposit($income->amount,$income->currency);
			Movement::create([
				'type'=>"IN",
				'description'=>$income->description,
				'amount'=>$income->amount,
				'currency'=>$income->currency,
				'date'=>date('Y-m-d')
			]);
			$this->response(['errors'=>false,'data'=>"Operacion Exitosa"]);
		}
		else{
			http_response_code(401);
			$this->response(['errors'=>true,'data'=>"Operacion Fallida"]);
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