<?php

namespace App\Models;

function setDateFormat($date, $format) {
    $newDate = date($format, strtotime($date));

    return $newDate;
}
class Client extends \ActiveRecord\Model {
    public static $belongs_to = [
        ['agent'],
        ['collector', 'class_name'=>'User', 'foreign_key'=>'collector_id'],
    ];

    public static $has_many = [['policies']];

    public function serialized() {
        $r = $this->to_array([
            'include'=> [
                'agent',
                'collector',
                'policies'=> [
                    'include'=> ['plan'],
                    'methods'=> ['company', 'totals'],
                ],
            ],
        ]);
        $r['h_id'] = (string) $this->h_id;

        return $r;
    }

    public function isLinkedToHubSpot() {
        return $this->h_id != null;
    }

    public function calculateDebt() {
        $amountToPay = $this->prima;
        $payed = 0;
        foreach ($this->payments as $payment) {
            $amountToPay = $amountToPay - $payment->calculateDiscount();
            if ($payment->currency === 'BOB') {
                $real = round($payment->amount / $payment->change_rate, 2);
                $payed = $payed + $real;
            } else {
                $payed = $payed + $payment->amount;
            }
        }

        return $amountToPay - $payed;
    }

    public function linkToHubSpot() {
        $apikey = 'abcb7c3c-c65a-4985-bc11-58892ac09f3f';
        $poliza = $this->policy_number;
        $company = $this->company;
        $search = new \stdClass();
        $search->filterGroups = [
            [
                'filters'=> [
                    [
                        'propertyName'=> 'poliza',
                        'operator'    => 'EQ',
                        'value'       => $poliza,
                    ],
                ],
            ],

        ];
        $search->properties = ['email', 'poliza', 'compa_a'];

        /** Start curl */
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_POSTFIELDS     => json_encode($search),
            CURLOPT_URL            => 'https://api.hubapi.com/crm/v3/objects/contacts/search?hapikey='.$apikey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo $err;

            return false;
        } else {
            $r = json_decode($response, true);

            if (isset($r['results'][0])) {
                $this->h_id = $r['results'][0]['id'];
                $this->save();
            }
        }
    }

    public function addHubSpotNote($text) {
        if (!$this->isLinkedToHubSpot()) {
            $this->linkToHubSpot();
        }
        $apikey = 'abcb7c3c-c65a-4985-bc11-58892ac09f3f';
        $h_id = $this->h_id;
        $data = [
            'engagement'=> [
                'active'=> true,
                'type'  => 'NOTE',

            ],
            'associations'=> [
                'contactIds'=> [$this->h_id],
            ],
            'metadata'=> [
                'body'=> $text,
            ],
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_URL            => 'https://api.hubapi.com/engagements/v1/engagements?hapikey='.$apikey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            return true;
        }
    }
}
