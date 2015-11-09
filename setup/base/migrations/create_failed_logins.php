<?php

use Phinx\Migration\AbstractMigration;

class CreateFailedLogins extends AbstractMigration
{
	public function change(){
		$users = $this->table('failed_logins');
		$users->addColumn('email', 'string', array('limit' => 150))
					->addColumn('date_attempted', 'datetime', array('default' => null))
					->save();
	}
}
