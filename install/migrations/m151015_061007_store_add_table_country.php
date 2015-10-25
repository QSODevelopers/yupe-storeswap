<?php

class m151015_061007_store_add_table_country extends yupe\components\DbMigration
{
	public function safeUp()
	{
		$this->createTable('{{store_country}}', [
			'id'     => 'pk',
			'name'   => 'varchar(50) NOT NULL',

		], $this->getOptions());
	}

	public function safeDown()
	{
		$this->dropTable('{{store_country}}');
	}
}