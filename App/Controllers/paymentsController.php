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
	public function create(){
		$policy=\App\Models\Policy::find([$this->payload['policy_id']]);
		$payment = array_diff_key($this->payload, array_flip(["tags"]));
		$payment['user_id']=$this->current_id;
		$payment=new \App\Models\Payment($payment);
		
		if($payment->save()){
			$payment->policy->client->addHubSpotNote('(SIS-COB) Cobranza efectuada en sistema por un monto de '.$payment->currency.' '.$payment->amount);
			if(isset($this->payload['tags'])){
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
		$payments = Payment::all(['order'=>'processed ASC,payment_date DESC','conditions'=>['processed = 0']]);
		foreach($payments as $payment){
			$result[]=$payment->to_array([
				'include'=>[
					'account',
					'policy'=>[
						'include'=>[
							'client',
							'plan'=>[
								'include'=>[
									'company'
								]
							]

						],
					]
				]
			]);
			
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
		if($payment->process()){
			$this->response(['errors'=>false,'data'=>'Validated Successfully']);
		}
		else{
			http_response_code(400);
			$this->response(['errors'=>true,'data'=>'Payment was not validated']);
		}
		
		
	}
}

?>