<?php

use yii\db\Migration;

/**
 * Class m201012_130508_add_table_currency_history
 */
class m201012_130508_add_table_currency_history extends Migration
{
    public $table = 'currency_history';
    public $table_currency = 'currency';
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable($this->table, [
            'id'                => $this->primaryKey()->comment('Id'),
            'id_currency'       => $this->integer()->notNull()->comment('Ид валюты'),
            'date'              => $this->integer()->notNull()->defaultValue(0)->comment('date'),
            'sale'              => $this->float()->notNull()->defaultValue(0)->comment('Продажа'),
            'buy'               => $this->float()->notNull()->defaultValue(0)->comment('Покупка'),

            'status_view'   => $this->smallInteger()->notNull()->defaultValue(1)->comment('status view'),

            'status_del'    => $this->smallInteger()->notNull()->defaultValue(0)->comment('status del'),
            'created_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date create'),
            'updated_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date update'),
            'deleted_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date delete'),
        ], $tableOptions . " COMMENT = 'Валюта'");

        $createIndexs = [
            ['date', 'status_del'],
            ['status_del'],
        ];

        foreach ($createIndexs as $key => $index)
            $this->createIndex("IDX-{$this->table}-{$key}", $this->table, $index);

        $this->addForeignKeyFast($this->table, 'id_currency', $this->table_currency);

    }
    public function down()
    {
        $this->execute("SET foreign_key_checks = 0;");
        $this->dropTable($this->table);
        $this->execute("SET foreign_key_checks = 1;");
        return true;
    }

    private function addForeignKeyFast($table, $column, $refTable)
    {
        $this->addForeignKey("FK-$table-$refTable",
            $table, $column,
            $refTable, 'id',
            'NO ACTION', 'NO ACTION');
    }
}
