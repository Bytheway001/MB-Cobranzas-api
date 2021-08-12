<?php

namespace App\Models;

class Payment extends Model
{
    public static $belongs_to = [['policy'], ['user'], ['account']];
    public static $before_create = ['set_creator'];
    public function set_creator() {
        $this->user_id = \Core\Request::instance()->user->id;
    }

    public function get_check(){
        return \App\Models\Check::find(['conditions'=>['payment_id = ?', $this->id]]);
    }

    public function serialize() {
        $payment = $this->to_array();
        $payment['payment_date'] = $this->payment_date->format('d-m-Y');
        $payment['client'] = $this->policy->client->first_name;
        $payment['collector'] = $this->policy->client->collector->name ?? '--';
        $payment['plan'] = $this->policy->plan;
        $payment['company'] = $this->policy->plan->company->name;
        $payment['account_name'] = $this->account ? $this->account->name : '--';
        $payment['payment_method'] = $this->serializePaymentMethods($this->payment_method);
        return $payment;
    }

    public function calculateDiscount() {
        $discount = $this->agency_discount + $this->agent_discount + $this->company_discount;
        if ($this->currency === 'BOB') {
            return $discount / $this->change_rate;
        } else {
            return $discount;
        }
    }

    private function serializePaymentMethods($method) {
        $methods = [
            'cash_to_agency'            => 'Efectivo la agencia',
            'check_to_agency_local'     => 'Cheque local a la agencia',
            'check_to_agency_foreign'   => 'Cheque extranjero a la agencia',
            'transfer_to_agency_foreign'=> 'Transferencia a la agencia (Exterior)',
            'transfer_to_agency_local'  => 'Transferencia la agencia (Local)',
            'claim_to_company'          => 'Abono de reclamo',
            'tdc_to_collector'          => 'Tarjeta de Credito A la Aseguradora',
            'check_to_foreign_company'  => 'Cheque extranjero a la Aseguradora',
            'transfer_to_company'       => 'Transferencia Bancaria a la Aseguradora',
            'tdc_to_company'            => 'Pago en portal de cliente(TDC)',
            'other_credit_card'         => 'Pago con tarjeta de Terceros'
        ];

        return $methods[$method];
    }

    public function isCheck() {
        return $this->payment_method == 'Cheque local a la agencia' || $this->payment_method == 'Cheque extranjero a la agencia';
    }

    public function get_payment_method() {
        return $this->serializePaymentMethods($this->read_attribute('payment_method'));
    }

    public function isAgencyPayment() {
        $agencyMethods = ['cash_to_agency', 'check_to_agency_foreign', 'check_to_agency_local', 'transfer_to_agency_foreign', 'transfer_to_agency_local','other_credit_card'];
        return in_array($this->payment_method, $agengyMethods);
    }

    public function isCash() {
        return $this->payment_method === 'cash_to_agency';
    }

    public function process() {
        if ($this->account and !$this->isCheck()) {
            $this->account->deposit($this->amount, $this->currency);
        }
        return $this->save();
    }

    public function revert($user_id) {
        if ($this->isCheck()) {
            $check = \App\Models\Check::find(['conditions'=>['payment_id = ?', $this->id]]);
            $check_id = $check->id;
            $account_to_withdraw_from = $check->wasCollected()?$check->account_id:9;
            
            $expense = new \App\Models\Expense([
                'date'       => date('Y-m-d H:i:s'),
                'account_id' => $account_to_withdraw_from,
                'category_id'=> 97,
                'user_id'    => $user_id,
                'description'=> 'Correccion de Cobranzas #'.$this->id,
                'currency'   => $this->currency,
                'amount'     => $this->amount,
                'office'     => 'sc', 'bill_number'=>'S/N',
            ]);
            if ($expense->save()) {
                $expense->reload();
                $this->corrected_with = $expense->id;
                $this->save();
            } else {
                print_r('Expense not saved');
                die();
            }
            if ($check) {
                $check->revert($expense->id);
            }
        } elseif ($this->payment_method == 'Pago con Tarjeta de Terceros') {
            $pp = PolicyPayment::find(['conditions'=>["currency = $this->currency and amount = $this->amount and DATE(created_at) = $this->created_at->format('Y-m-d')"]]);
            $pp->revert();
        } else {
            if ($this->account_id) {
                $expense = new \App\Models\Expense([
                    'date'       => date('Y-m-d H:i:s'),
                    'account_id' => $this->account->id,
                    'category_id'=> 97,
                    'user_id'    => $user_id,
                    'description'=> 'Correccion de Cobranzas #'.$this->id,
                    'currency'   => $this->currency,
                    'amount'     => $this->amount,
                    'office'     => 'sc', 'bill_number'=>'S/N',
                ]);

                if ($expense->save()) {
                    $expense->reload();
                    $this->corrected_with = $expense->id;
                    $this->save();
                } else {
                    print_r($expense->errors->full_messages());
                    die();
                }
            } else {
                $this->delete();
            }
        }

        return true;
    }
}
