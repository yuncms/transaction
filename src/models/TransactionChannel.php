<?php

namespace yuncms\transaction\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yuncms\behaviors\JsonBehavior;
use yuncms\helpers\Json;
use yuncms\db\ActiveRecord;
use yuncms\transaction\Channel;
use yuncms\validators\JsonValidator;

/**
 * This is the model class for table "{{%transaction_channels}}".
 *
 * @property int $id Id
 * @property string $identity Channel Identity
 * @property string $name Channel Name
 * @property string $title Channel Title
 * @property string $description Channel Description
 * @property string $className Channel className
 * @property array $configuration Channel configuration
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
            'configuration' => [
                'class' => JsonBehavior::class,
                'attributes' => ['configuration'],
            ],
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
            [['description'], 'string', 'max' => 255],
            [['className'], 'classExists'],
            [['configuration'], JsonValidator::class],
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
     * Validate class exists
     */
    public function classExists()
    {
        if (!class_exists($this->className)) {
            $message = Yii::t('yuncms', "Unknown class '{class}'", ['class' => $this->className]);
            $this->addError('className', $message);
            return;
        }
        if (!is_subclass_of($this->className, Channel::class)) {
            $message = Yii::t('yuncms/transaction', "'{class}' must extend from 'yuncms\\transaction\\Channel' or its child class", [
                'class' => $this->className]);
            $this->addError('className', $message);
        }
    }

    /**
     * 保存前执行
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    /**
     * 保存后执行
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
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
