<?php

namespace App\Models;

use \App\Models\Renewal;
use DateTime;

class Policy extends \ActiveRecord\Model {
    public static $belongs_to = [
        ['client'],
        ['plan'],
    ];
    public static $has_many = [
        ['payments','conditions'=>'corrected_with is null'],
        ['policy_payments'],
        ['renewals']
    ];
    /* Method override */
    public static function find() {
        $class = get_called_class();
        if (func_num_args() <= 0) {
            throw new RecordNotFound("Couldn't find $class without an ID");
        }
        $args = func_get_args();
        $options = static::extract_and_validate_options($args);
        $num_args = count($args);
        $single = true;

        if ($num_args > 0 && ($args[0] === 'all' || $args[0] === 'first' || $args[0] === 'last')) {
            switch ($args[0]) {
                case 'all':
                $single = false;
                break;

                case 'last':
                if (!array_key_exists('order', $options)) {
                    $options['order'] = join(' DESC, ', static::table()->pk) . ' DESC';
                } else {
                    $options['order'] = SQLBuilder::reverse_order($options['order']);
                }

                    // fall thru

                    // no break
                case 'first':
                $options['limit'] = 1;
                $options['offset'] = 0;
                break;
            }

            $args = array_slice($args, 1);
            $num_args--;
        }
        //find by pk
        elseif (1 === count($args) && 1 == $num_args) {
            $args = $args[0];
        }

        // anything left in $args is a find by pk
        if ($num_args > 0 && !isset($options['conditions'])) {
            return static::find_by_pk($args, $options)->getLastPolicy();
        }

        $options['mapped_names'] = static::$alias_attribute;
        $list = static::table()->find($options);
        $result=[];
        foreach ($list as $l) {
            $result[]=$l->getLastPolicy();
        }
        return $single ? (!empty($list) ? $list[0]->getLastPolicy() : null) : $result;
    }

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
            'renewals'=>[],
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

        foreach ($this->renewals as $renewal) {
            $result['renewals']=$pp->to_array();
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

    public function getLastPolicy() {
        $last_renewal = Renewal::last(['select'=>'plan_id,option,premium,frequency,renovation_date','conditions'=>['policy_id = ?',$this->id]]);

        if (!$last_renewal) {
            return $this;
        } else {
            foreach ($last_renewal->to_array() as $key=>$item) {
                if ($key==='renovation_date') {
                    $this->$key=$last_renewal->$key;
                    
                    $this->$key=$this->$key->format('Y-m-d');
                } else {
                    $this->$key = $item;
                }
            }
            return $this;
        }
    }

    public function getStatus() {
        $startDate=new DateTime($this->renovation_date);
        $endDate= clone $startDate;
        $startDate=$startDate->format('Y-m-d');
        $endDate = $endDate->add(new \DateInterval('P1Y'));
        $endDate = $endDate->format('Y-m-d');
        $payments = \App\Models\Payment::all(['conditions'=>[
            'policy_id = ? AND DATE(payment_date) BETWEEN ? AND ? AND corrected_with is null',
            $this->id,
            $startDate,
            $endDate,
        ]
    ]);
        if (count($payments)===0) {
            return "Nueva";
        }

        $payed = 0;
        $discounts = 0;

        foreach ($payments as $payment) {
            if ($payment->currency==="BOB") {
                $discounts += ($payment->agency_discount + $payment->agent_discount + $payment->company_discount)/$payment->change_rate;
                $payed += $payment->amount/$payment->change_rate;
            } else {
                $discounts += $payment->agency_discount + $payment->agent_discount + $payment->company_discount;
                $payed += $payment->amount;
            }
        }
        $debt = $this->premium - $discounts -$payed;
        if ($debt===0) {
            return "Cobrada";
        } else {
            switch ($this->frequency) {
                case "Annual":
                return $debt>0?"Pendiente":"Cobrada";
                break;

                default:
                return "N/A";
                break;
            }
        }
    }
}
