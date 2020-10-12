<?php

use yii\db\Migration;

/**
 * Class m190729_151410_add_table_log
 */
class m190729_151410_add_table_log extends Migration
{
    public $table = 'log';
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        ///////////////////////////////////////////////////////////
        /// Yii2 standard log
        ///
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey(),
            'level' => $this->integer(),
            'category' => $this->string(),
            'log_time' => $this->double(),
            'prefix' => $this->text(),
            'message' => $this->text(),
            'is_new' => $this->boolean()->notNull()->defaultValue(true),
        ], $tableOptions);

        $this->createIndex('idx_log_level', $this->table, 'level');
        $this->createIndex('idx_log_category', $this->table, 'category');

    }

    public function down()
    {
        $this->execute("SET foreign_key_checks = 0;");
        $this->dropTable($this->table);
        $this->execute("SET foreign_key_checks = 1;");
        return true;
    }

/** ================================================= */
//        $createIndexs = [
//            ['id_category', 'status_del'],
//        ];
//
//        $this->MyCreateIndex($createIndexs);
//        $this->addForeignKeyFast($this->table, 'id_user', 'user');

//    private function addForeignKeyFast($table, $column, $refTable) {
//        $this->addForeignKey("FK-$table-$refTable",
//            $table, $column,
//            $refTable, 'id',
//            'NO ACTION', 'NO ACTION');
//    }
//
//    private function MyCreateIndex($createIndexs) {
//        foreach ($createIndexs as $key => $index)
//            $this->createIndex("IDX-{$this->table}-{$key}", $this->table, $index);
//
//        return true;
//    }
/** ================================================= */
}
