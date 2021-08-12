<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use App\Models\Policy;
use App\Models\Client;
use App\Models\Agent;
use App\Models\User;

final class PolicyTest extends TestCase
{
    public static function setUpBeforeClass():void {
        ActiveRecord\Config::initialize(function ($cfg) {
            ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';
            $cfg->set_default_connection('development');
            $cfg->set_model_directory('./App/Models');
            $cfg->set_connections(['development' => 'mysql://root:@localhost/mb_cobranzas;charset=utf8', ]);
        });
    }

    public function testAssociation() {
        $this->assertEquals(true, true);
    }

    public function policiesProvider() {
    }
}
