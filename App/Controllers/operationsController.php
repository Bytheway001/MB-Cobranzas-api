<?php

namespace App\Controllers;

use App\Models\Account;
use App\Models\Payment;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Transfer;
use App\Models\PolicyPayment;
use Core\Request;
use Core\Response;

class operationsController extends Controller
{
	public function createTransfer() {
		$operation = new \App\Operations\CreateTransferOperation();
		$operation->process();
		if($operation->done){
			Response::send($operation->statusCode,$operation->response);
		}  
		else{
			Response::crash($operation->statusCode,$operation->errors);
		}
	}

	public function createIncome() {
		$operation = new \App\Operations\CreateIncomeOperation();
		$operation->process();
		if($operation->done){
			Response::send($operation->statusCode,$operation->response);
		}  
		else{
			Response::crash($operation->statusCode,$operation->errors);
		}
	}

	public function collect_check() {
		$operation = new \App\Operations\CollectCheckOperation();
		$operation->process();
		if($operation->done){
			Response::send($operation->statusCode,$operation->response);
		}  
		else{
			Response::crash($operation->statusCode,$operation->errors);
		}
		
	}

	public function convert() {
		$operation = new \App\Operations\ConvertCurrencyOperation();
		$operation->process();
		if($operation->done){
			Response::send($operation->statusCode,$operation->response);
		}  
		else{
			Response::crash($operation->statusCode,$operation->errors);
		}
	}

	public function reportCorrection() {
		$payload = Request::instance()->payload;
		$user_id = Request::instance()->user->id;
		switch ($payload['type']) {
			case 'expenses':
			$operation = new \App\Operations\Revert\RevertExpenseOperation();
			$operation->process();
			break;
			case 'incomes':
			$operation = new \App\Operations\Revert\RevertIncomeOperation();
			$operation->process();
			break;
			case 'payments':

			$operation = new \App\Operations\Revert\RevertPaymentOperation();
			$operation->process();
			
			break;
			case 'policy_payments':
			$operation = new \App\Operations\Revert\RevertPolicyPaymentOperation();
			$operation->process();
			break;
			default:
			break;

		}
		if($operation->done){
			Response::send($operation->statusCode,$operation->response);
		}  
		else{
			Response::crash($operation->statusCode,$operation->errors);
		}
	}
}
