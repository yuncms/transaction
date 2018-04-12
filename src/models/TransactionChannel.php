<?php

namespace yuncms\transaction\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yuncms\db\ActiveRecord;

/**
 * This is the model class for table "{{%transaction_channels}}".
 *
 * @property int $id Id
 * @property string $identity Channel Identity
 * @property string $name Channel Name
 * @property string $title Channel Title
 * @property string $description Channel Description
 * @property string $className Channel className
 * @property array $extra Channel extra
 * @property int $created_at Created At
 * @property int $updated_at Updated At
 */
class TransactionChannel extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transaction_channels}}';
    }

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['identity', 'name', 'title'], 'required'],
            [['identity', 'name', 'title'], 'string', 'max' => 64],
            [['className', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yuncms', 'Id'),
            'identity' => Yii::t('yuncms/transaction', 'Channel Identity'),
            'name' => Yii::t('yuncms/transaction', 'Channel Name'),
            'title' => Yii::t('yuncms/transaction', 'Channel Title'),
            'description' => Yii::t('yuncms/transaction', 'Description'),
            'className' => Yii::t('yuncms/transaction', 'Channel ClassName'),
            'configuration' => Yii::t('yuncms/transaction', 'Configuration'),
            'created_at' => Yii::t('yuncms/transaction', 'Created At'),
            'updated_at' => Yii::t('yuncms/transaction', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     * @return TransactionChannelQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionChannelQuery(get_called_class());
    }
}
