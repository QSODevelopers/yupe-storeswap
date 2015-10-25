<?php

class m151019_061007_store_alter_country extends yupe\components\DbMigration
{
	public function safeUp()
	{
		$this->alterColumn('{{store_country}}', 'name', 'varchar(250) NOT NULL');
	}

	public function safeDown()
	{
		$this->alterColumn('{{store_country}}', 'name', 'varchar(50) NOT NULL');
	}
}