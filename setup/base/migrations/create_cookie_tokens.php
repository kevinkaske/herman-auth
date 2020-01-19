<?php

use Phinx\Migration\AbstractMigration;

class CreateCookieTokens extends AbstractMigration{
		public function change(){
			try {
				$sql = '';
				$sql = $sql.'CREATE TABLE `cookie_tokens` (';
				$sql = $sql.'  `id` integer(11) UNSIGNED NOT NULL AUTO_INCREMENT,';
				$sql = $sql.'  `selector` char(12),';
				$sql = $sql.'  `hashedValidator` char(64),';
				$sql = $sql.'  `user_id` integer(11) UNSIGNED NOT NULL,';
				$sql = $sql.'  `expires` datetime,';
				$sql = $sql.'  PRIMARY KEY (`id`)';
				$sql = $sql.') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
				$row = $this->fetchRow($sql);
			}catch(Exception $e){

			}
		}
}
