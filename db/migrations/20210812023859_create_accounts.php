<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAccounts extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */


    public function change(): void
    {
       $decimal = ['scale'=>2,'precision'=>10];
       $varchar = ['limit'=>50];
       $table = $this->table('accounts',['collation'=>'utf8_spanish2_ci']);
       $table->addColumn('name','string',$varchar)
       ->addColumn("USD",'decimal',$decimal)
       ->addColumn("BOB",'decimal',$decimal)
       ->addColumn("type",'string',$varchar)
       ->addColumn("last_balance_usd",'decimal',$decimal)
       ->addColumn("last_balance_bob",'decimal',$decimal)
       ->addColumn("last_balance_date",'date')
       ->create();
   }
}
