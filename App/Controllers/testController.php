<?php
namespace App\Controllers;

use App\Models\Policy;
use App\Models\Payment;
use App\Libs\Validator;
use Core\Response;

class testController extends Controller
{
    public function main() {
       $expenses = \App\Models\Expense::all();
       foreach($expenses as $expense){
            if($expense->corrected_with){
                $income = \App\Models\Income::find([$expense->corrected_with]);
                $income->update_attributes(['correcting'=>$expense->id,'correcting_type'=>'expense']);
            }
       }
       $incomes = \App\Models\Income::all();
       foreach($incomes as $income){
            if($income->corrected_with){
                $expense = \App\Models\Expense::find([$income->corrected_with]);
                $expense->update_attributes(['correcting'=>$income->id,'correcting_type'=>'income']);
            }
       }
    }
}
