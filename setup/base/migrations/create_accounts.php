<?php
use Phinx\Migration\AbstractMigration;

class CreateAccounts extends AbstractMigration{
	public function change(){
		$users = $this->table('accounts');
		$users->addColumn('account_name', 'string', array('limit' => 40))
					->addColumn('api_key', 'string', array('limit' => 100))
					->addColumn('created', 'datetime')
					->addColumn('updated', 'datetime', array('default' => null))
					->save();
	}
}
