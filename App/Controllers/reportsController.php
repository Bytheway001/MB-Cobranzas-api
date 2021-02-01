<?php

namespace App\Controllers;

use App\Models\Payment;

class reportsController extends Controller {
    public function getReports() {
        $from = $_GET['f'] ?? '01/'.date('m/Y');
        $to = $_GET['t'] ?? cal_days_in_month(CAL_GREGORIAN, 8, 2009).'/'.date('m/Y');

        $result = [
            'expenses'       => [],
            'policy_payments'=> [],
            'payments'       => [],
            'checks'         => [],
            'incomes'        => [],
            'pending'        => [],
        ];

        if ($from && $to) {
            if (isset($_GET['id'])) {
                $payments = \App\Models\Payment::all(['conditions'=>['DATE(created_at) BETWEEN ? AND ? and user_id = ?', $this->setDateFormat($from, 'Y-m-d'), $this->setDateFormat($to, 'Y-m-d'), $_GET['id']]]);
                $expenses = \App\Models\Expense::all(['order'=>'date DESC', 'conditions'=>['DATE(date) BETWEEN ? AND ? and user_id = ?', $this->setDateFormat($from, 'Y-m-d'), $this->setDateFormat($to, 'Y-m-d'), $_GET['id']]]);
                $policy_payments = \App\Models\PolicyPayment::all(['conditions'=>['DATE(created_at) BETWEEN ? AND ? and user_id = ?', $this->setDateFormat($from, 'Y-m-d'), $this->setDateFormat($to, 'Y-m-d'), $_GET['id']]]);
                $incomes = \App\Models\Income::all(['order'=>'date DESC', 'conditions'=>['DATE(date) BETWEEN ? AND ? and user_id = ?', $this->setDateFormat($from, 'Y-m-d'), $this->setDateFormat($to, 'Y-m-d'), $_GET['id']]]);
            } else {
                $payments = \App\Models\Payment::all(['conditions'=>['DATE(created_at) BETWEEN ? AND ?', $this->setDateFormat($from, 'Y-m-d'), $this->setDateFormat($to, 'Y-m-d')]]);
                $expenses = \App\Models\Expense::all(['order'=>'date DESC', 'conditions'=>['DATE(date) BETWEEN ? AND ?', $this->setDateFormat($from, 'Y-m-d'), $this->setDateFormat($to, 'Y-m-d')]]);
                $policy_payments = \App\Models\PolicyPayment::all(['conditions'=>['DATE(created_at) BETWEEN ? AND ?', $this->setDateFormat($from, 'Y-m-d'), $this->setDateFormat($to, 'Y-m-d')]]);
                $incomes = \App\Models\Income::all(['order'=>'date DESC', 'conditions'=>['DATE(date) BETWEEN ? AND ?', $this->setDateFormat($from, 'Y-m-d'), $this->setDateFormat($to, 'Y-m-d')]]);
            }
        } else {
            if (isset($_GET['id'])) {
                $payments = \App\Models\Payment::all(['order'=>'created_at DESC', 'conditions'=>['user_id = ?', $_GET['id']]]);
                $expenses = \App\Models\Expense::all(['order'=>'date DESC', 'conditions'=>['user_id = ?', $_GET['id']]]);
                $policy_payments = \App\Models\PolicyPayment::all(['order'=>'created_at DESC', 'conditions'=>['user_id = ?', $_GET['id']]]);
                $incomes = \App\Models\Income::all(['order'=>'date DESC', 'conditions'=>['user_id = ?', $_GET['id']]]);
            } else {
                $payments = \App\Models\Payment::all(['order'=>'created_at DESC']);
                $expenses = \App\Models\Expense::all(['order'=>'date DESC']);
                $policy_payments = \App\Models\PolicyPayment::all(['order'=>'created_at DESC']);
                $incomes = \App\Models\Income::all(['order'=>'date DESC']);
            }
        }

        foreach ($payments as $payment) {
            $result['payments'][] = $payment->to_array([
                'include'=> [
                    'account',
                    'policy'=> [
                        'include'=> [
                            'client',
                            'plan'=> [
                                'include'=> [
                                    'company',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        }
        foreach ($expenses as $expense) {
            $result['expenses'][] = $expense->to_array(['include'=>['category', 'account', 'user']]);
        }
        foreach ($policy_payments as $policy_payment) {
            $result['policy_payments'][] = $policy_payment->to_array([
                'include'=> [
                    'account',
                    'policy'=> [
                        'include'=> [
                            'client',
                            'plan'=> ['include'=>'company'],
                        ],
                    ],
                ],
            ]);
        }

        foreach ($incomes as $income) {
            $result['incomes'][] = $income->to_array(['include'=>['category', 'account', 'user']]);
        }
        foreach (\App\Models\Check::all() as $check) {
            $c = $check->to_array();
            $c['client'] = $check->client->first_name;

            if ($check->status == 'Abonado en cuenta') {
                $c['collected'] = $check->collected_at->format('d-m-Y');
            } else {
                $c['collected'] = '--';
            }
            $result['checks'][] = $c;
        }

        $this->response($result);
    }

    public function payments_per_company() {
        $result = [];
        foreach (Payment::all() as $payment) {
            $company = $payment->client->company;
            $office = $payment->city;
            if (!isset($result[$company])) {
                $result[$company] = [];
            }
            if (!isset($result[$company][$office])) {
                $result[$company][$office] = [];
            }
            if (!isset($result[$company][$office][$payment->payment_method])) {
                $result[$company][$office][$payment->payment_method] = 0;
            }

            $result[$company][$office][$payment->payment_method] = $result[$company][$office][$payment->payment_method] + $payment->amount;
        }

        $this->response($result);
        exit();
    }

    private function setDateFormat($date, $format) {
        $date = str_replace('/', '-', $date);
        $newDate = date($format, strtotime($date));

        return $newDate;
    }

    public function accountMovements($id) {
        $initialdate = isset($_GET['period']) ? new \DateTime(date('Y-'.$_GET['period'].'-01')) : new \DateTime('first day of this month');
        $finaldate = clone $initialdate;
        $finaldate->modify('last day of this month');
        $initialdate = $initialdate->format('Y-m-d');

        $finaldate = $finaldate->format('Y-m-d');
        $account = \App\Models\Account::find([$id]);

        $result['saldos'] = $account->getSaldoAt($initialdate);
        
        $result['query'] = "SELECT * from movimiento_de_cuenta where account_id = $id and date BETWEEN '$initialdate' and '$finaldate'";
        $result['movements'] = [];
        $data = \App\Models\Income::find_by_sql("SELECT * from movimiento_de_cuenta where account_id = $id and date BETWEEN '$initialdate' and '$finaldate'");
        foreach ($data as $row) {
            $r = $row->to_array();
            $r['date'] = $row->date->format('d-m-Y');
            $result['movements'][] = $r;
        }
        $this->response(['errors'=>false, 'data'=>$result]);
    }
}
