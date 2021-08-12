<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use App\Models\Payment;
use App\Models\Policy;

final class PaymentFlowTest extends TestCase
{
    public static function setUpBeforeClass():void {
        ActiveRecord\Config::initialize(function ($cfg) {
            ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';
           
            $cfg->set_model_directory('./App/Models');
            $cfg->set_connections([
                'development' => 'mysql://root:@localhost/mb_cobranzas;charset=utf8',
                'test' => 'mysql://root:@localhost/mb_cobranzas_test;charset=utf8'
             ]);
            $cfg->set_default_connection('test');
        });
    }

    public function setUp():void {
        $policy = Policy::last();
        if (!$policy) {
            Policy::create(['client_id'=>7,'policy_number'=>12345,'premium'=>2000,'plan_id'=>1,'renovation_date'=>'2021-01-01','effective_date'=>'2021-01-01','created_by'=>21]);
            $policy = Policy::last();
        }
    }

    public function testAccountUpdateSaldoAfterPaymentCreation() {
        $payment = new Payment([
            'user_id'=>21,
            'account_id'=>110,
            'payment_method'=>'cash_to_agency',
            'payment_type'=>'complete',
            'payment_date'=>'2021-01-01',
            'agency_discount'=>0,
            'agent_discount'=>0,
            'company_discount'=>0,
            'amount'=>1000,
            'currency'=>'USD',
            'change_rate'=>1,
            'office'=>'SC'
        ]);
        $saldo_before = $payment->account->usd;
        if ($payment->save()) {
            $this->assertEquals($saldo_before, $payment->account->usd);
        } else {
            die("NOT");
        }
    }
}
