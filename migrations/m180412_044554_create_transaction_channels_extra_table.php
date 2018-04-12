<?php

use yuncms\db\Migration;

/**
 * Handles the creation of table `transaction_channels_extra`.
 */
class m180412_044554_create_transaction_channels_extra_table extends Migration
{
    /**
     * @var string The table name.
     */
    public $tableName = '{{%transaction_channels_extra}}';

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
        $this->createTable($this->tableName, [
            'channel_id' => $this->unsignedInteger()->notNull()->comment('Channel Id'),
            'extra' => $this->text(),
            'created_at' => $this->integer()->notNull()->comment('Created At'),//创建时间
            'updated_at' => $this->integer()->notNull()->comment('Updated At'),//更新时间
        ], $tableOptions);

        $this->addForeignKey('transaction_channels_extra_fk_2', $this->tableName, 'channel_id', '{{%transaction_channels}}', 'id', 'CASCADE', 'RESTRICT');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
