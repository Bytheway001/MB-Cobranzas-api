<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\Models\Client;
use App\Models\Policy;
use App\Models\Agent;
use App\Models\User;

final class ClientsTest extends TestCase
{
    public function setUp():void {
        $this->client = ['first_name'=>null,'comment'=>'This is a test client','agent_id'=>'88','collector_id'=>21,'email'=>"",'policy_type'=>'','phone'=>null,'h_id'=>12];
    }

    public static function setUpBeforeClass():void {
        ActiveRecord\Config::initialize(function ($cfg) {
            ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';
            $cfg->set_default_connection('development');
            $cfg->set_model_directory('./App/Models');
            $cfg->set_connections(['development' => 'mysql://root:@localhost/mb_cobranzas;charset=utf8', ]);
        });
    }
    /**
    * @dataProvider clientsProvider
    */
    public function testModelValidity(Client $client, $expected) {
        $this->assertEquals($expected, $client->is_valid());
    }
    /**
    * @dataProvider clientsProvider
    */
    public function xtestClientShouldBelongToCollector(Client $client) {
    }

    public function clientsProvider() {
        ActiveRecord\Config::initialize(function ($cfg) {
            ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';
            $cfg->set_default_connection('development');
            $cfg->set_model_directory('./App/Models');
            $cfg->set_connections(['development' => 'mysql://root:@localhost/mb_cobranzas;charset=utf8', ]);
        });
        $result=[];
        $clients=[
            'valid_client'=>[['first_name'=>'Rafael','collector_id'=>21,"agent_id"=>87,"h_id"=>15],true],
            'client_without_h_id'=>[['first_name'=>'Rafael','collector_id'=>21,"agent_id"=>87],false],
            'client_without_first_name'=>[['collector_id'=>21,"agent_id"=>87,'h_id'=>15],false],
            'client_without_collector'=>[['first_name'=>'Rafael',"agent_id"=>87,"h_id"=>15],false],
            'client_without_agent'=>[['first_name'=>'Rafael','collector_id'=>21,"h_id"=>15],false]
        ];

        foreach ($clients as $key=>$client) {
            $c = new Client($client[0]);

            $result[$key]=[$c,$client[1]];
        }
        
        return $result;
    }
}
