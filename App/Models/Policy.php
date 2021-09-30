<?php

namespace App\Models;

use \App\Models\Renewal;
use \App\Models\Payment;
use \App\Models\PolicyPayment;
use DateTime;

class Policy extends Model
{
    public static $belongs_to = [
        ['client'],
        ['plan'],
    ];

    public static $has_many = [
        ['payments','conditions'=>'corrected_with is null'],
        ['policy_payments','conditions'=>'corrected_with is null'],
        ['renewals','order'=>'id ASC']
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

    public function get_actual_payments() {
        if (count($this->renewals)===0) {
            return Payment::all(['conditions'=>['policy_id = ? and corrected_with is null',$this->id]]);
        } else {
            return Payment::all(['conditions'=>['policy_id = ? and corrected_with is null and created_at > ?',$this->id,end($this->renewals)->created_at]]);
        }
    }

    public function get_actual_policy_payments() {
        if (count($this->renewals)===0) {
            return PolicyPayment::all(['conditions'=>['policy_id = ? and corrected_with is null ',$this->id]]);
        } else {
            return PolicyPayment::all(['conditions'=>['policy_id = ? and corrected_with is null and created_at > ?',$this->id,end($this->renewals)->created_at]]);
        }
    }

    public function company() {
        try {
            return $this->plan->company->to_array();
        } catch (\Exception $e) {
            print_r("La poliza ".$this->id, 'No tiene una compañia asignada');
            exit();
        }
    }

    public function totals() {
        return [
            'discounts'=>$this->discounts,
            'payed'    => $this->payed,
            'collected'=> $this->collected,
            'financed' => $this->financed,
            'debt'=>$this->debt
        ];
    }

    /**
    * Cobranzas Realizadas a esta poliza
    */
    public function get_collected():float {
        $total = 0;
        foreach ($this->actual_payments as $cobranza) {
            $r[]=$cobranza->id;
            if ($cobranza->currency==="BOB") {
                $total=$total+ ($cobranza->amount/$cobranza->change_rate);
            } else {
                $total=$total+ $cobranza->amount;
            }
        }
        return $total;
    }

    /**
    * Pagos hechos a la aseguradora
    */
    public function get_payed():float {
        $direct_methods = ['tdc_to_collector','tdc_to_company','transfer_to_company','check_to_foreign_company','claim_to_company'];
        $total = 0;
        foreach ($this->actual_payments as $payment) {
            if (in_array($payment->payment_method, $direct_methods)) {
                if ($payment->currency==="BOB") {
                    $total=$total+ ($payment->amount/$payment->change_rate);
                } else {
                    $total=$total+ $payment->amount;
                }
            }
        }
        
        foreach ($this->actual_policy_payments as $pp) {
            if ($pp->currency==="BOB") {
                $total += round($pp->amount / 6.96, 2);
            } else {
                $total +=  $pp->amount;
            }
        }

        return $total;
    }

    /**
    * Descuentos hechos a esta poliza
    */
    public function get_discounts():array {
        $discounts=['agency'=>0,'agent'=>0,'company'=>0];
        foreach ($this->actual_payments as $p) {
            if ($p->currency==='BOB') {
                $discounts['agency']+=round($p->agency_discount/$p->change_rate, 2);
                $discounts['agent']+=round($p->agent_discount/$p->change_rate, 2);
                $discounts['company']+=round($p->company_discount/$p->change_rate, 2);
            } else {
                $discounts['agency']+=$p->agency_discount;
                $discounts['agent']+=$p->agent_discount;
                $discounts['company']+=$p->company_discount;
            }
        }
        return $discounts;
    }

    /**
    * Deuda Real con la Agencia
    */
    public function get_debt() {
        return round($this->premium - array_sum($this->discounts) - $this->collected, 2);
    }

    /**
    * Financiamientos
    */
    public function get_financed() {
        $financed = $this->payed - array_sum($this->discounts) - $this->collected;
        return $financed>0?round($financed, 2):0;
    }

    public function get_periods() {
        foreach ($this->renewals as $i=>$r) {
        }
    }

    public function history() {
        $result = [];
        if (count($this->renewals)===0) {
            $periodName=$this->effective_date->format('Y').'-'.$this->renovation_date->format('Y');
            $result[$periodName]=['payments'=>[],'policy_payments'=>[]];
            $payments = \App\Models\Payment::all(['conditions'=>['policy_id = ?',$this->id]]);
            $policy_payments = \App\Models\PolicyPayment::all(['conditions'=>['policy_id = ? and corrected_with is null',$this->id]]);
            foreach ($payments as $p) {
                $result[$periodName]['payments'][]=$p->to_array(['include'=>'user']);
            }
            foreach ($policy_payments as $pp) {
                $result[$periodName]['policy_payments'][]=$pp->to_array(['include'=>'user']);
            }
        }
        foreach ($this->renewals as $i=>$r) {
            $ends_on = $r->renovation_date->format('Y');
            $starts_on = $ends_on - 1;
            if ($i==0) {
                $periodName=$this->effective_date->format('Y').'-'.$starts_on;
                
                $result[$periodName]=['policy_payments'=>[],'payments'=>[]];
                $payments = \App\Models\Payment::all(['conditions'=>['policy_id = ? and created_at < ? and corrected_with is null',$this->id,$r->created_at]]);
                foreach ($payments as $p) {
                    $result[$periodName]['payments'][]=$p->to_array(['include'=>'user']);
                }
                $policy_payments = \App\Models\PolicyPayment::all(['conditions'=>['policy_id = ? and created_at < ? and corrected_with is null',$this->id,$r->created_at]]);
                foreach ($policy_payments as $pp) {
                    $result[$periodName]['policy_payments'][]=$pp->to_array(['include'=>'user']);
                }
            }
            $periodName = ($ends_on - 1).'-'.$ends_on;
            $result[$periodName]=['policy_payments'=>[],'payments'=>[]];
            if (array_key_exists($i+1, $this->renewals)) {
                $payments=\App\Models\Payment::all(['conditions'=>['policy_id = ? and created_at > ? and created_at < ? and corrected_with is null',$this->id,$r->created_at,$this->renewals[$i+1]->created_at]]);
                foreach ($payments as $p) {
                    $result[$periodName]['payments'][]=$p->to_array(['include'=>'user']);
                }
                $policy_payments = \App\Models\PolicyPayment::all(['conditions'=>['policy_id = ? and created_at > ? and created_at < ? and corrected_with is null',$this->id,$r->created_at,$this->renewals[$i+1]->created_at]]);
                foreach ($policy_payments as $pp) {
                    $result[$periodName]['policy_payments'][]=$pp->to_array(['include'=>'user']);
                }
            } else {
                $payments=\App\Models\Payment::all(['conditions'=>['policy_id = ? and created_at > ? and corrected_with is null',$this->id,$r->created_at]]);
                foreach ($payments as $p) {
                    $result[$periodName]['payments'][]=$p->to_array(['include'=>'user']);
                }
                $policy_payments = \App\Models\PolicyPayment::all(['conditions'=>['policy_id = ? and created_at > ? and corrected_with is null',$this->id,$r->created_at]]);
                foreach ($policy_payments as $pp) {
                    $result[$periodName]['policy_payments'][]=$pp->to_array(['include'=>'user']);
                }
            }
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
        $last_renewal = Renewal::last(['select'=>'plan_id,`option`,premium,frequency,renovation_date,created_at','conditions'=>['policy_id = ?',$this->id]]);

        if (!$last_renewal) {
            return $this;
        } else {
            foreach ($last_renewal->to_array() as $key=>$item) {
                if ($key==='renovation_date') {
                    $this->$key=$last_renewal->$key;
                } else {
                    $this->$key = $item;
                }
            }
            return $this;
        }
    }

    public function status() {
        $isTotallyPayed = $this->payed === $this->premium - $this->discounts['company'];
        if ($this->collected === 0 and $this->payed ===0) {
            return "Nueva";
        }
        if ($this->debt>0) {
            if ($this->payed > $this->collected) {
                return "Financiada";
            } else {
                return "Pendiente";
            }
        }
        if ($this->debt==0 and !$isTotallyPayed) {
            return "Cobrada";
        }
        if ($this->debt == 0 and $isTotallyPayed) {
            return "Pagada";
        }
        if ($this->debt<0) {
            return $this->debt;
        }
    }

    public function getLastRenewalObject() {
        $last_renewal = Renewal::last(['select'=>'id,plan_id,`option`,premium,frequency,renovation_date,created_at','conditions'=>['policy_id = ?',$this->id]]);
        if ($last_renewal) {
            return $last_renewal;
        } else {
            return false;
        }
    }

    public function get_isNew(){
        return count($this->renewals) <= 0;
    }
}
