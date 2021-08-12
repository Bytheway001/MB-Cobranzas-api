<?php
namespace App\Controllers;

use App\Models\Payment;
use App\Models\PolicyPayment;
use App\Models\Renewal;
use App\Models\Policy;
use Core\Request;
use Core\Response;

class exportsController extends Controller
{
    public function __construct() {
        $this->month = !empty($_GET['month'])?$_GET['month']:date('m');
        $this->year = !empty($_GET['year'])?$_GET['year']:date('Y');
        $this->initialdate = new \DateTime(date("$this->year-$this->month-01")) ;
        $this->finaldate = new \DateTime(date('y-m-t', strtotime("$this->year-$this->month-01"))) ;
        $month = \App\Libs\Translate::monthNames($this->initialdate->format('n'));
        $this->periodName = '_'.$month.'_'.$this->initialdate->format('Y');
    }
    public function cashReport($id) {
        $initialdatestring = $this->initialdate->format('Y-m-d');
        $finaldatestring = $this->finaldate->format('Y-m-d');
        $account = \App\Models\Account::find([$id]);
        $result['saldos'] = $account->getSaldoAt($initialdatestring);
        $result['movements'] = [];
        $data = \App\Models\Income::find_by_sql("SELECT date,category,bill_number as compte,description,currency,debe,haber from movimiento_de_cuenta where account_id = $id and DATE(date) BETWEEN '$initialdatestring' and '$finaldatestring'");
        
        foreach ($data as $row) {
            $r = $row->to_array();
            $r['date'] = $row->date->format('d-m-Y');

            $result['movements'][] = $r;
        }

        $account_name = $account->name;
        $filename=str_replace(" ", "_", $account_name).$this->periodName;
        $file = new \App\Reports\CashReport("Reporte de Caja");
        $file->setValue('A2', 'Reporte de Cuenta ('.$account_name.')');
        $file->setValue('A3', "Periodo del ".$this->initialdate->format('d-m-Y')." Al ".$this->finaldate->format('d-m-Y'));
        $file->writeData($result['movements'], $result['saldos']);
        $file->format();
        $base64data=$file->base64($filename);
        Response::send(200, ['file'=>$base64data,'filename'=>$filename]);
    }

    public function financedReport() {
        $policies = \App\Models\Policy::all();
        $data=[];
        foreach ($policies as $policy) {
            if ($policy->financed>1) {
                $data[]=[
                    'client'=>$policy->client->first_name,
                    'company'=>$policy->plan->company->name,
                    'financed'=>$policy->financed
                ];
            }
        }
        $file = new \App\Reports\FinancedReport("Reporte de Financiados");
        $filename = "Financiamientos".$this->periodName;
        $file->writeData($data);
        Response::send(200, ['file'=>$file->base64($filename),'filename'=>$filename]);
    }

    public function paymentsReport() {
        $initialdatestring = $this->initialdate->format('Y-m-d');
        $finaldatestring = $this->finaldate->format('Y-m-d');
        $data=[];
        $payments = \App\Models\Payment::all(['order'=>'payment_date ASC','conditions'=>[
            'payment_date BETWEEN ? and ?',
            $initialdatestring,
            $finaldatestring
        ]]);
        foreach ($payments as $payment) {
            $data[]=[
                'policy'=>$payment->policy->policy_number,
                'client'=>$payment->policy->client->first_name,
                'company'=>$payment->policy->plan->company->name,
                'date'=>$payment->payment_date->format('d-m-Y'),
                'method'=>$payment->payment_method,
                'account'=>$payment->account?$payment->account->name:"N/A",
                'premium'=>$payment->policy->premium,
                'company_discount'=>$payment->company_discount??0,
                'agency_discount'=>$payment->agency_discount??0,
                'agent_discount'=>$payment->agent_discount??0,
                'currency'=>$payment->currency,
                'amount'=>$payment->amount
            ];
        }

        $file = new \App\Reports\PaymentReport('Reporte de Cobranzas');
        $file->setValue('A1', "REPORTE DE COBRANZAS ".str_replace('_', ' ', $this->periodName));
        $filename = "Cobranzas".$this->periodName;
        $file->writeData($data);
        Response::send(200, ['file'=>$file->base64($filename),'filename'=>$filename]);
    }

    public function mainReport() {
        $initialdatestring = $this->initialdate->format('Y-m-d');
        $finaldatestring = $this->finaldate->format('Y-m-d');
        $data=['payments'=>[],'paid'=>[],'pending'=>[],'policy_payments'=>[],'renewals'=>[]];
        $payments = Payment::all(['conditions'=>['payment_date BETWEEN ? and ?',$initialdatestring,$finaldatestring]]);
        foreach ($payments as $payment) {
            $data['payments'][]=[
                'client'=>$payment->policy->client->first_name,
                'date'=>$payment->payment_date->format('d-m-Y'),
                'premium'=>$payment->policy->premium,
                'amount'=>$payment->amount,
                'company_discount'=>$payment->company_discount,
                'agency_discount'=>$payment->agency_discount,
                'payment_method'=>$payment->payment_method
            ];
        }
        
        $policy_payments = PolicyPayment::all(['conditions'=>['payment_date BETWEEN ? and ?',$initialdatestring,$finaldatestring]]);
        foreach ($policy_payments as $pp) {
            $data['policy_payments'][]=[
                'client'=>$pp->policy->client->first_name,
                'company'=>$pp->policy->plan->company->name,
                'date'=>$pp->payment_date->format('d-m-Y'),
                'premium'=>$pp->policy->premium,
                'amount'=>$pp->amount,
                'payment_method'=>($pp->account_id!==null)?$pp->account->name:"Tarjeta de Terceros",
                'comment'=>$pp->comment
            ];
        }

        $pending = Policy::all();
        foreach ($pending as $pp) {
            if ($pp->collected>$pp->payed) {
                $data['pending_policies'][]=[
                    'client'=>$pp->client->first_name,
                    'company'=>$pp->plan->company->name,
                    'premium'=>$pp->premium,
                    'amount'=>$pp->collected,
                    'comment'=>$pp->comment
                ];
            }
        }
        foreach (Renewal::all() as $renewal) {
            $data['renewals'][]=[
                'policy'=>$renewal->policy->policy_number,
                'client'=>$renewal->policy->client->first_name,
                'renewal_date'=>$renewal->created_at->format('d-m-Y'),
                'plan'=>$renewal->plan->name,
                'option'=>$renewal->option,
                'premium'=>$renewal->premium,
                'frequency'=>$renewal->frequency
            ];
        }
        $filename = "Reporte General".$this->periodName;
        $file= new \App\Reports\MainReport('Reporte General', $this->initialdate, $this->finaldate);
        $file->writeData($data);
        Response::send(200, ['file'=>$file->base64($filename),'filename'=>$filename]);
    }
}
