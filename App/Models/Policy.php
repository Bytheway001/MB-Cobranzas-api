<?php

namespace App\Models;

use DateTime;

class Policy extends \ActiveRecord\Model {
    public static $belongs_to = [
        ['client'],
        ['plan'],
    ];
    public static $has_many = [
        ['payments','conditions'=>'corrected_with is null'],
        ['policy_payments']
    ];

    public function company() {
        try {
            return $this->plan->company->to_array();
        } catch (\Exception $e) {
            print_r($this);
            exit();
        }
    }

    public function getDiscounts() {
        $discounts=[
            'agency'=>0,
            'agent'=>0,
            'company'=>0
        ];
        $payments = $this->payments;
        foreach ($this->payments as $p) {
            if ($p->currency==='BOB') {
                $discounts['agency']+=round($p->agency_discount/$p->change_rate, 2);
                $discounts['agent']+=round($p->agent_discount/$p->change_rate, 2);
                $discounts['company']+=round($p->company_discount/$p->change_rate, 2);
            } else {
                $discounts['agency']+=$p->agency_discount/$p->change_rate;
                $discounts['agent']+=$p->agent_discount/$p->change_rate;
                $discounts['company']+=$p->company_discount/$p->change_rate;
            }
        }
        return $discounts;
    }

    public function totals() {
        return [
        'discounts'=>$this->getDiscounts(),
        'payed'    => $this->totalpayed(),
        'collected'=> $this->totalcollected(),
        'financed' => $this->totalfinanced(),
    ];
    }

    public function totalcollected() {
        $cobranzas = $this->payments;
        $total = 0;
        foreach ($cobranzas as $cobranza) {
            if ($cobranza->corrected_with === null && $cobranza->processed === 1) {
                if ($cobranza->currency === 'BOB') {
                    $total = $total + ($cobranza->amount / $cobranza->change_rate);
                } else {
                    $total = $total + $cobranza->amount;
                }
            }
        }

        return $total;
    }

    public function totalpayed() {
        $discounts = array_sum($this->getDiscounts());
        $policy_payments = $this->policy_payments;
        $total = 0;
        foreach ($policy_payments as $pp) {
            if ($pp->currency === 'BOB') {
                $total = $total + round($pp->amount / 6.96, 2);
            } else {
                $total = $total + $pp->amount;
            }
        }

        return $total-$discounts;
    }

    public function totalfinanced() {
        $policy_payments = $this->policy_payments;
        $total = 0;
        $payed = $this->totalpayed();
        $collected = $this->totalcollected();

        return $payed - $collected < 0 ? 0 : $payed - $collected;
    }

    public function history() {
        $result = [
        'payments'       => [],
        'policy_payments'=> [],
    ];

        foreach ($this->payments as $payment) {
            if (!$payment->corrected_with) {
                $result['payments'][] = $payment->to_array();
            }
        }

        foreach ($this->policy_payments as $pp) {
            $result['policy_payments'][] = $pp->to_array();
        }

        return $result;
    }

    /* Fecha en la cual comienza la poliza actual */
    public function begginingDate() {
        $now = new DateTime('now');
        $this_year_renovation_date = new DateTime(date('Y').'-'.$this->effective_date->format('m-d'));
        /* Si aun no ha pasado la fecha de renovacion devolvemos la fecha del año pasado */
        if ($now < $this_year_renovation_date) {
            $date = $this_year_renovation_date->sub(new \DateInterval('P1Y'));
        } else {
            $date = $this_year_renovation_date;
        }

        return $date->format('Y-m-d');
    }

    /* Fechas en las cuales se espera el pago */
    public function getPaymentDates() {
        $last_renovation = new DateTime($this->begginingDate());
        $dates = [$last_renovation->format('Y-m-d')];
        switch ($this->frequency) {
        case 'Semiannual':
        for ($i = 0; $i < 1; $i++) {
            $dates[] = $last_renovation->add(new \DateInterval('P6M'))->format('Y-m-d');
        }
        break;

        case 'Quarterly':
        for ($i = 0; $i < 3; $i++) {
            $dates[] = $last_renovation->add(new \DateInterval('P3M'))->format('Y-m-d');
        }
        break;

        case 'Monthly':
        for ($i = 0; $i < 11; $i++) {
            $dates[] = $last_renovation->add(new \DateInterval('P1M'))->format('Y-m-d');
        }
        break;
    }

        return $dates;
    }
}
