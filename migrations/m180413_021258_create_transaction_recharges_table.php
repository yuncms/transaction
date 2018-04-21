<?php

use yuncms\db\Migration;

/**
 * Handles the creation of table `transaction_recharges`.
 */
class m180413_021258_create_transaction_recharges_table extends Migration
{
    /**
     * @var string The table name.
     */
    public $tableName = '{{%transaction_recharges}}';

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
        //创建充值表
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'user_id' => $this->unsignedInteger()->notNull()->comment('User Id'),
            'user_fee' => $this->decimal(12, 2),//用户手续费
            'balance_bonus' => '',
            'from_user' => '',
            'balance_transaction_id' => $this->bigInteger()->unsigned()->notNull(),//关联的余额明细表ID
            'description' => $this->string(),//附加说明，最多 255 个 Unicode 字符。
            'metadata' => $this->text(),//元数据
        ], $tableOptions);

        $this->addForeignKey('transaction_recharges_fk_1', $this->tableName, 'user_id', '{{%user}}', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('transaction_recharges_fk_2', $this->tableName, 'balance_transaction_id', '{{%transaction_balance_transaction}}', 'id', 'CASCADE', 'RESTRICT');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
