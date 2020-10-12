<?php

use yii\db\Migration;

/**
 * Class m201012_130318_add_table_coin
 */
class m201012_130318_add_table_currency extends Migration
{
    public $table = 'currency';
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable($this->table, [
            'id'            => $this->primaryKey()->comment('Id'),
            'key'           => $this->string(255)->notNull()->defaultValue('')->comment('key'),
            'name'          => $this->string(255)->notNull()->defaultValue('')->comment('name'),

            'status_view'   => $this->smallInteger()->notNull()->defaultValue(1)->comment('status view'),

            'status_del'    => $this->smallInteger()->notNull()->defaultValue(0)->comment('status del'),
            'created_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date create'),
            'updated_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date update'),
            'deleted_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date delete'),
        ], $tableOptions . " COMMENT = 'Валюта'");

    }
    public function down()
    {
        $this->execute("SET foreign_key_checks = 0;");
        $this->dropTable($this->table);
        $this->execute("SET foreign_key_checks = 1;");
        return true;
    }
}
