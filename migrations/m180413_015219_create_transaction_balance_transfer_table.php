<?php

use yuncms\db\Migration;

/**
 * Handles the creation of table `transaction_balance_transfer`.
 */
class m180413_015219_create_transaction_balance_transfer_table extends Migration
{
    /**
     * @var string The table name.
     */
    public $tableName = '{{%transaction_balance_transfer}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        //创建余额转账表
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
