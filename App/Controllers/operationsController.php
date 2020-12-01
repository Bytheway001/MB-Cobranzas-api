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
				\App\Models\Expense::create([
					'user_id'=>$this->current_id,
					'bill_number'=>'S/N',
					'description'=>'Transferencia A '.$transfer->destiny->name,
					'currency'=>$transfer->currency,
					'amount'=>$transfer->amount,
					'account_id'=>$transfer->origin->id,
					'category_id'=>73,
					'date'=>date('Y-m-d H:i:s')
				]);
				\App\Models\Income::create([
					'user_id'=>$this->current_id,
					'description'=>'Transferencia Desde '.$transfer->origin->name,
					'currency'=>$transfer->currency,
					'amount'=>$transfer->amount,
					'account_id'=>$transfer->destiny->id,
					'category_id'=>73,
					'date'=>date('Y-m-d H:i:s')
					
				]);
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
			$this->response(['errors'=>false,'data'=>$income->to_array(['include'=>['account','category']])]);
		}
		else{
			http_response_code(401);
			$this->response(['errors'=>true,'data'=>"Operacion Fallida"]);
		}
	}

	public function collect_check(){
		$check = \App\Models\Check::find([$this->payload['check_id']]);
		$account = Account::find([$this->payload['account_id']]);
		$check->status = 'Abonado en cuenta';
		$check->account_id = $account->id;
		$check->save();
		$check->reload();
		
		$check->account->deposit($check->amount,$check->currency);
		$a=Account::find_by_name('Cheques en Transito');
		$a->withdraw($check->amount,$check->currency);
		$a->save();
		$account->save();
		$this->response(['errors'=>false,'data'=>"Cheque abonado a cuenta"]);
	}
}

?>