<?php

namespace App\Controllers;

use App\Libs\Time;
use App\Models\Agent;
use App\Models\Client;
use App\Models\User;

class clientsController extends Controller
{
    private function clientExists($client)
    {
        return Client::count(['conditions'=>['policy_number = ? and company = ?', $client['policy_number'], $client['company']]]);
    }

    public function create()
    {
        if (!isset($this->payload['id'])) {
            $client = new Client($this->payload);
            if ($client->save()) {
                $this->response(['errors'=>false, 'data'=>$client->serialized()]);
            } else {
                $this->response(['errors'=>true, 'data'=>'No se pudo crear el cliente']);
            }
        } else {
            $client = Client::find([$this->payload['id']]);
            if ($client->update_attributes($this->payload)) {
                $this->response(['errors'=>false, 'data'=>$client->serialized()]);
            } else {
                $this->response(['errors'=>true, 'data'=>'No se pudo crear el cliente']);
            }
        }
    }

    // POST /clients/policies/create
    public function createPolicy()
    {
        $client = Client::find([$this->payload['client_id']]);
        $this->payload['renovation_date'] = Time::getAsDate('d/m/Y', $this->payload['renovation_date'])->format('Y-m-d');
        $this->payload['effective_date'] = Time::getAsDate('d/m/Y', $this->payload['effective_date'])->format('Y-m-d');
        if (!isset($this->payload['id'])) {
            $base_date = Time::getAsDate('d/m/Y', $this->payload['effective_date']);
            $this->payload['created_by'] = $this->current_id;
            $this->payload['created_at'] = Time::getasDate('d/m/y', date('d/m/y'))->format('Y-m-d H:i:s');
            if ($policy = $client->create_policy($this->payload)) {
                $this->response(['errors'=>false, 'data'=>$policy->reload()->to_array(['include'=>['plan'], 'methods'=>['company', 'totals']])]);
            } else {
                $this->response(['errors', 'data'=>'Unknown']);
            }
        } else {
            $policy = \App\Models\Policy::find([$this->payload['id']]);
            unset($this->payload['totals']);
            if ($policy->update_attributes($this->payload)) {
                $this->response(['errors'=>false, 'data'=>$policy->reload()->to_array(['include'=>['plan'], 'methods'=>['company', 'totals']])]);
            } else {
                $this->response(['errors', 'data'=>'Unknown']);
            }
        }
    }

    public function bulk()
    {
        $count = 0;
        foreach ($this->payload as $client) {
            $agent = Agent::find_by_name($client['agent']);
            if (!$agent) {
                $agent = Agent::create(['name'=>$client['agent']]);
            }
            if (isset($client['collector'])) {
                $collector = User::find_by_name($client['collector']);
                if (!$collector) {
                    $collector = User::create(['name'=>$client['collector']]);
                }
                $client['collector_id'] = $collector->id;
            } else {
                $client['collector_id'] = null;
            }
            $client['agent_id'] = $agent->id;

            $client['first_name'] = $client['name'];
            unset($client['name']);
            unset($client['agent']);
            unset($client['collector']);

            if (!$this->clientExists($client)) {
                Client::create($client);
            } else {
                $count = $count + 1;
            }
        }
        $this->response(['errors'=>false, 'data'=>"Clientes creados con exito, $count clientes ya existian y no han sido agregados"]);
    }

    public function index()
    {
        try {
            $clients = [];
            $result = [];
            if (isset($_GET['q'])) {
                $clients = Client::all(['conditions'=>['first_name LIKE ? OR h_id LIKE ?', '%'.$_GET['q'].'%', '%'.$_GET['q'].'%']]);
            } else {
                $clients = Client::all();
            }
            foreach ($clients as $client) {
                $result[] = $client->serialized();
            }
            $this->response(['errors'=>false, 'data'=>$result]);
        } catch (\ActiveRecord\DatabaseException $e) {
            echo Client::table()->conn->last_query;
            echo $e->getMessage();
        }
    }

    // GET /payments/:id
    public function getPayments()
    {
        $result = [];
        $client = \App\Models\Client::find([$id]);
        foreach ($client->payments as $payment) {
            $p = $payment->to_array();
            $p['payment_date'] = $payment->payment_date->format('d-m-Y');
            $result[] = $p;
        }
        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function updatePolicy($id)
    {
        $client = Client::find_by_id($id);
        if ($client) {
            if ($client->update_attributes($this->payload)) {
                $this->response(['errors'=>false, 'data'=>$client->serialized()]);
            } else {
                $this->response(['errors'=>true, 'data'=>'UPDATE_IMPOSSIBLE']);
            }
        } else {
            http_response_code(400);
            $this->response(['errors'=>true, 'data'=>'CLIENT_NOT_FOUND']);
        }
    }

    public function show($id)
    {
        $client = Client::find_by_id($id);
        $result = $client->serialized();
        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function profile($id)
    {
        $client = Client::find_by_id($id);
        $result = $client->serialized();
        $result['payments'] = [];
        foreach ($client->payments as $payment) {
            $p = $payment->to_array();
            $p['date'] = $payment->payment_date->format('d-m-Y');
            $p['user'] = $payment->user->name;
            $result['payments'][] = $p;
        }
        $result['policy_payments'] = [];
        foreach ($client->policy_payments as $p) {
            $pp = $p->to_array();
            $pp['date'] = $p->created_at->format('d-m-y');
            $pp['user'] = $p->user->name;
            $result['policy_payments'][] = $pp;
        }
        $this->response(['errors'=>false, 'data'=>$result]);
    }

    private function setDateFormat($date, $format)
    {
        $newDate = date($format, strtotime($date));

        return $newDate;
    }

    public function getRenovations()
    {
        $result = [];
        $policies = \App\Models\Policy::all();

        try {
            foreach ($policies as $policy) {
                if ($policy->renovation_date) {
                    if ($policy->renovation_date->format('Y-m') === $_GET['year'].'-'.$_GET['month']) {
                        $result[] = $policy->to_array(['include'=>'client']);
                    }
                } else {
                    print_r($policy);
                    exit();
                }
            }
        } catch (\Exception $e) {
            print_r($policy->id);
        }

        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function getPaymentsOfPolicy($policyId)
    {
        $result = [];
        $policy = \App\Models\Policy::find([$policyId]);
        $policyHasRenewals = count($policy->renewals) > 0;

        if (!$policyHasRenewals) {
            foreach ($policy->payments as $payment) {
                if ($payment->corrected_with === null) {
                    $result[$policy->renovation_date->format('Y') - 1][] = $payment->to_array();
                }
            }
        } else {
            /* Pagos Anteriores a la primera renovacion */
            foreach (\App\Models\Payment::all(['conditions'=>['policy_id = ? and payment_date < ? and corrected_with is null', $policy->id, $policy->renewals[0]->renovation_date]]) as $p) {
                $result[$policy->renovation_date->format('Y') - 1][] = $p->to_array();
            }
            /* Pagos despues de la nueva renovacion */
            foreach ($policy->renewals as $r) {
                $result[$r->renovation_date->format('Y')] = [];
                $startdate = new \DateTime($r->renovation_date->format('Y-m-d'));
                $endDate = clone $startdate;
                $endDate->add(new \DateInterval('P1Y'))->format('Y-m-d');
                $payments = \App\Models\Payment::all(['conditions'=>['policy_id = ? and payment_date BETWEEN ? AND ? and corrected_with is null', $policy->id, $startdate, $endDate]]);
                foreach ($payments as $p) {
                    $result[$r->renovation_date->format('Y')][] = $p->to_array();
                }
            }
        }

        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function getFinancedPolicies()
    {
        $result = [];
        $policies = \App\Models\Policy::all();
        foreach ($policies as $policy) {
            if ($policy->financed > 0) {
                $result[] = $policy->to_array([
                    'methods'=> ['totals'],
                    'include'=> [
                        'client',
                        'plan'=> [
                            'include'=> 'company',
                        ],

                    ], ]);
            }
        }

        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function getPolicy($id)
    {
        $policy = \App\Models\Policy::find([$id]);
        $this->response(['errors'=> false, 'data'=>$policy->to_array([
            'methods'=> 'company',
            'include'=> 'plan',
        ])]);
    }

    public function createRenewal()
    {
        $this->payload['renovation_date'] = Time::getAsDate('d/m/Y', $this->payload['renovation_date'])->format('Y-m-d');
        $renewal = new \App\Models\Renewal($this->payload);

        $renewal->user_id = $this->current_id;
        if ($renewal->save()) {
            $this->response(['errors'=>false, 'data'=>'Cliente Renovado con exito']);
        } else {
            http_response_code(400);
            $this->response(['errors'=>true, 'data'=>'No se pudo renovar al cliente']);
        }
    }
}
