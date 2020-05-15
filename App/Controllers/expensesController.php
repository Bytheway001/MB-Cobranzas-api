<?php 
namespace App\Controllers;
use \App\Models\Expense;
class expensesController extends Controller{
	public function create(){
		$expense = new Expense($this->payload);
		if($expense->save()){
			$this->response(['errors'=>false,'data'=>"Creado con exito"]);
		}
		else{
				$this->response(['errors'=>true,'data'=>"No se pudo crear"]);
		}
	}

	public function index(){
		$result=[];
		$expenses = Expense::all();
		foreach($expenses as $expense){
			$expense=$expense->to_array();
			$expense['date']=\App\Libs\Time::format($expense['date'],'d-m-Y');
			$result[] =$expense; 
		}

		$this->response(['errors'=>false,'data'=>$result]);
	}
}

 ?>