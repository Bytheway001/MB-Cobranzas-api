<?php 
namespace App\Controllers;
use \App\Models\Payment;

function clientHasDebt($client){
	$amount_to_pay = $client->prima;
	$payed_amount=0;
	$payments = $client->payments;
	foreach($payments as $payment){
		if($payment->currency === 'BOB'){
			$payed_amount = $payed_amount+($payment->amount / $payment->change_rate);
			$amount_to_pay = $amount_to_pay - $payment->calculateDiscount();
		}
		else{
			$payed_amount = $payed_amount+$payment->amount;
		}
	}
	return  $payed_amount < $client->prima;
}

class paymentsController extends Controller{
	
	/* Registro de cobranza */
	public function create(){
		
		/* Caso de pagos directos a la aseguradora */
		if(!$this->payload['payment']['account_id']){
			$this->payload['payment']['account_id'] = null;
		}
		$payment=new Payment($this->payload['payment']);
		$payment->user_id = $this->current_id;
		if(!$payment->client->isLinkedToHubSpot()){
			$payment->client->linkToHubSpot();
		}

		/* Creamos el cheque si es un pago en cheque */
		if($payment->isCheck()){
			$check= \App\Models\Check::create(['amount'=>$payment->amount,'currency'=>$payment->currency,'client_id'=>$payment->client_id]);
			$check->save();
		}

		/* Guardamos, creamos la nota de  hubspot y el movimiento de cuenta (si es caja)*/
		if($payment->save()){
			$client=$payment->client;
			if($payment->payment_type==='complete'){
				$client->status='Cobrada';
				$client->save();
			}

			else{
				if(clientHasDebt($client)){
					$client->status='Pendiente';
					$client->save();
				}
			}

			//$payment->client->addHubSpotNote('(SIS-COB) Cobranza efectuada en sistema por un monto de '.$payment->currency.' '.$payment->amount);
			if($payment->account_id){
				$payment->account->deposit($payment->amount,$payment->currency);
			}
			
			if($payment->isCash()){
				\App\Models\Movement::create(['date'=>date('Y-m-d'),'type'=>"IN",'description'=>"Cobranza ".$payment->client->first_name,'amount'=>$payment->amount,'currency'=>$payment->currency,'destiny'=>$payment->account->id]);
			}
			if($this->payload['tags']){

				$users=\App\Models\User::all(['conditions'=>['name in (?)',$this->payload['tags']]]);
				$tags = array_map(function($t){return ['name'=>$t->name,'email'=>$t->email];},$users);
				$mailer = new \App\Libs\Mailer($tags,\Core\View::get_partial('partials','payment_created',$payment));
				$mailer->mail->send();
				
			}
			$this->response(['errors'=>false,'data'=>"Cobranza Registrada exitosamente"]);
		}
		else{
			$this->response(['errors'=>true,'data'=>"No se pudo registrar la cobranza"]);
		}
	}

	public function index(){
		$result=[];
		$payments = Payment::all(['order'=>'processed ASC,payment_date DESC']);
		foreach($payments as $payment){
			$payment=$payment->to_array();
			$client = \App\Models\Client::find([$payment['client_id']]);
			$payment['payment_date']=\App\Libs\Time::format($payment['payment_date'],'d-m-Y');
			$payment['client']=$client->first_name;
			$payment['collector']=$client->collector->name;
			$payment['plan']=$client->plan.'/'.$client->option;
			$payment['company']=$client->company->name;
			$result[] = $payment;

		}

		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function getClientPayments($id){
		$result=[];
		$client=\App\Models\Client::find([$id]);
		foreach($client->payments as $payment){
			$p=$payment->to_array();
			$p['payment_date']=$payment->payment_date->format('d-m-Y');
			$result[]=$p;
		}
		$this->response(['errors'=>false,'data'=>$result]);
	}

	public function validate($id){
		$payment=Payment::find([$id]);
		$payment->processed=1;
		$payment->save();
		$this->response(['errors'=>false,'data'=>'Validated Successfully']);
	}
}

?>