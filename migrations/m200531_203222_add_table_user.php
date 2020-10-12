<?php

use yii\db\Migration;

/**
 * Class m200531_203222_add_table_user
 */
class m200531_203222_add_table_user extends Migration
{
    public $table = 'user';
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable($this->table, [
            'id'                    => $this->primaryKey()->comment('Id'),
            'avatar'                => $this->string(100)->notNull()->defaultValue('')->comment('avatar'),
            'type'                  => $this->integer()->notNull()->defaultValue(1)->comment('type user'),
            'username'              => $this->string(255)->notNull()->defaultValue('')->comment('name'),
            'name'                  => $this->string(255)->notNull()->defaultValue('')->comment('name'),
            'surname'               => $this->string(255)->notNull()->defaultValue('')->comment('surname'),
            'email'                 => $this->string(255)->notNull()->unique()->comment('email'),
            'access_token'          => $this->string(32)->notNull()->defaultValue('')->comment('access token'),
            'auth_key'              => $this->string(32)->notNull()->defaultValue('')->comment('auth key'),
            'password_hash'         => $this->string()->notNull()->comment('password hash'),
            'password_reset_token'  => $this->string()->unique()->comment('password reset token'),
            'verification_token'    => $this->string()->defaultValue(null)->comment('verification token'),
            'last_online'           => $this->integer()->notNull()->defaultValue(0)->comment('time_online'),

            'is_admin'      =>  $this->boolean()->notNull()->defaultValue(0)->comment('is admin'),

            'status_view'   => $this->smallInteger()->notNull()->defaultValue(1)->comment('status view'),

            'status_del'    => $this->smallInteger()->notNull()->defaultValue(0)->comment('status del'),
            'created_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date create'),
            'updated_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date update'),
            'deleted_at'    => $this->integer()->notNull()->defaultValue(0)->comment('date delete'),
        ], $tableOptions . " COMMENT = 'Пользователь'");

        $createIndexs = [
            ['last_online', 'status_del'],
            ['type', 'status_del'],
            ['is_admin', 'status_del'],
            ['access_token', 'status_del'],
            ['auth_key', 'status_del'],
            ['password_hash', 'status_del'],
            ['password_reset_token', 'status_del'],
            ['verification_token', 'status_del'],
            ['status_view', 'status_del'],
        ];

        foreach ($createIndexs as $key => $index)
            $this->createIndex("IDX-{$this->table}-{$key}", $this->table, $index);

    }
    public function down()
    {
        $this->execute("SET foreign_key_checks = 0;");
        $this->dropTable($this->table);
        $this->execute("SET foreign_key_checks = 1;");
        return true;
    }
}
