<?php 
namespace App\Operations;
use \Core\{Request,Response,ApiException};
use \App\Models\{Policy,Income,Account};
class CreatePaymentOperation extends Operation implements IOperation{
	private $payment_data;
	private $receiving_policy;
	private $tags = [];


/*
	1.- Validamos los datos
	2.- Obtenemos las direcciones de email a notificar del pago
	3.- Obtenemos la poliza
	4.- Creamos el pago
	5.- Depositamos en la cuenta correspondiente
	6.- Si el pago es un cheque creamos dicho cheque
	7.- Si paga con tarjeta de terceros creamos el pago de poliza
	8.- Agregamos la nota correspondiente en el hubspot
	9.- Hacemos el envio de email
*/
	public function __construct(){
		parent::__construct();
		$this->payment_data =  $payment_params = array_diff_key($this->payload, array_flip(['receiving_policy','tags']));
		$this->receiving_policy = $this->payload['receiving_policy']??null;
	}

	public function process(){
		try{
			$this->validateRequest();
			$this->setTags();
			$this->getPolicy();
			$this->saveIntoDB();
			$this->depositIntoAccount();
			$this->createCheck();
			$this->createPolicyPayment();
			$this->addHubspotNote();
			$this->sendEmails();
			$this->prepareResponse();
		}
		catch(ApiException $e){
			$this->statusCode=403;
			$this->response = $this->errors;
		}
		catch(\Exception $e){
			die($e->getMessage());
		}
		
	}

	public function setTags(){
		if(!empty($this->payload['tags'])){
			$this->tags = $this->payload['tags'];
		}
	}
	public function addHubSpotNote(){
		$payment = $this->payment;
		$payment->policy->client->addHubSpotNote('(SIS-COB) Cobranza efectuada en sistema por un monto de '.$payment->currency.' '.$payment->amount);
	}

	public function validateRequest():void{
		extract($this->payment_data);
		// Payment must have a client
		if(empty($policy_id)){
			$this->errors['policy']='Must Be Present';
		}
		// Payment must have a payment_method
		if(empty($payment_method)){
			$this->errors['payment_method']="Must be present";
		}
		if(empty($amount)){
			$this->errors['amount']="Must be present and greater than 0";
		}
		if(empty($currency)){
			$this->errors['currency']="Must be present";
		}
		if(count($this->errors)>0){
			$this->statusCode = 403;
			throw new ApiException("Bad Request");
		}
	}

	public function getPolicy(){
		$policy =  \App\Models\Policy::find([$this->payment_data['policy_id']]);
		if(!$policy){
			$this->errors['policy']="Was not found";
			return;
		}
		$this->policy = $policy;
	}

	public function saveIntoDB(){
		$this->payment_data['user_id']=\Core\Request::instance()->user->id;
		$payment = $this->policy->build_payment($this->payment_data);
		if($payment->isCheck()){
			$payment->account_id = 9;
		}
		$payment->save();
		$this->payment = $payment;
	}

	public function DepositIntoAccount(){

		if($this->payment->account_id){
			$this->payment->account->deposit($this->payment->amount,$this->payment->currency);
		}
	}

	public function createCheck(){
		$payment = $this->payment;
		if($payment->isCheck()){
			$payment->policy->client->create_check(
				[
					'amount'=>$payment->amount,
					'currency'=>$payment->currency,
					'payment_id'=>$payment->id,
					'account_id'=>$payment->account_id
				]
			);
		}
	}

	public function createPolicyPayment(){

		$payment = $this->payment; 
		if($payment->payment_method === 'Pago con tarjeta de Terceros'){
			$receiving_policy = Policy::find([$this->receiving_policy]);
			$receiving_policy->create_policy_payment([
				'policy_id'=>$this->receiving_policy,
				'currency'=>$payment->currency,
				'comment'=>'Pagada con TC del cliente '.$payment->policy->client->first_name,
				'user_id'=>Request::instance()->user->id,
				'payment_type'=>'Direct',
				'payment_date'=>$payment->payment_date,
				'amount'=>$payment->amount,
				'account_id'=>null,
			]);
		}
	}

	public function sendEmails(){

		$payment = $this->payment;
		if(count($this->tags)>0){
			$users = \App\Models\User::all(['conditions'=>['name in (?)', $this->tags]]);
			$tags = array_map(function ($t) {
				return ['name'=>$t->name, 'email'=>$t->email];
			}, $users);
			foreach ($tags as $tag) {
				$user = \App\Models\User::find_by_email($tag['email']);
				\App\Models\Notification::create(['user_id'=>$user->id,'message'=>Request::instance()->user->name.' '.'Ha creado una cobranza de '.$payment->amount.' '.$payment->currency.' A la poliza de '.$payment->policy->client->first_name]);
			}
			$mailer = new \App\Libs\Mailer($tags, \Core\View::get_partial('partials', 'payment_created', $payment));
			$mailer->mail->send();
		}
	}

	public function prepareResponse(){
		try{
			if(count($this->errors)>0){
				$this->connection->rollback();
				$this->response = $this->errors;
			}
			$this->connection->commit();
			$this->done = true;
			$this->statusCode = 201;
			$this->response='Payment Created Successfully';
		}
		catch(\Exception $e){
			$this->connection->rollback();
			$this->response="Exception! ".$e->getMessage();
		}
	}

}









?>