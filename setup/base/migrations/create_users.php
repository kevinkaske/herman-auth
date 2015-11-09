<?php
use Phinx\Migration\AbstractMigration;

class CreateUsers extends AbstractMigration{
	public function change(){
		$users = $this->table('users');
		$users->addColumn('account_id', 'integer')
					->addColumn('email', 'string', array('limit' => 40))
					->addColumn('password', 'string', array('limit' => 100))
					->addColumn('first_name', 'string', array('limit' => 30))
					->addColumn('last_name', 'string', array('limit' => 30))
					->addColumn('admin', 'boolean', array('default' => 0))
					->addColumn('created', 'datetime')
					->addColumn('updated', 'datetime', array('default' => null))
					->addIndex(array('email'), array('unique' => true))
					->save();
	}
}
