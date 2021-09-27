<?php
namespace App\Models;

use App\Models\Income;

class Check extends Model
{
    public static $belongs_to = [['client'], ['account'],['payment']];
    /*
    public function revert($expense_id) {
        $incomes = Income::all(['conditions'=>["amount = $this->amount and currency = ?",$this->currency]]);
        foreach ($incomes as $income) {
            $checknumber = explode('#', $income->description);
            if ($this->id == $checknumber[1]) {
                $income->corrected_with = $expense_id;
                $income->save();
                return;
            }
        }
        static::delete();
    }

    public function deposit_into_transit() {
        Income::create([
            'account_id'=>9,
            'category_id'=>96,
            'description'=>'Cobranza '.$this->payment->policy->client->first_name.' Cheque # '.$this->id,
            'currency'=>$this->currency,
            'amount'=>$this->amount,
            'date'=>$this->payment->payment_date
        ]);
    }
     */
    public function wasCollected() {
       
        return $this->account_id !== 9;
    }
}
