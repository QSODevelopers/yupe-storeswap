<?php

class m151015_061007_store_create_country_foreign_key extends yupe\components\DbMigration
{
	public function safeUp()
	{
		$this->addColumn('{{store_product}}', 'country_id', 'integer');

		$this->addForeignKey("fk_{{store_country}}_type", "{{store_product}}", "country_id", "{{store_country}}", "id", "SET NULL", "CASCADE");
	}

	public function safeDown()
	{
		$this->dropColumn('{{store_product}}', 'country_id');
	}
}