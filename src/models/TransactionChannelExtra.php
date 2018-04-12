<?php

namespace yuncms\transaction\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yuncms\db\ActiveRecord;
use yuncms\user\models\User;

/**
 * This is the model class for table "{{%transaction_channels_extra}}".
 *
 * @property int $id Id
 * @property int $user_id User Id
 * @property int $channel_id Channel Id
 * @property string $extra
 * @property int $created_at Created At
 * @property int $updated_at Updated At
 *
 * @property User $user
 * @property TransactionChannel $channel
 */
class TransactionChannelExtra extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transaction_channels_extra}}';
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
            [['user_id', 'channel_id'], 'required'],
            [['user_id', 'channel_id'], 'integer'],
            [['extra'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['channel_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransactionChannel::class, 'targetAttribute' => ['channel_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Id'),
            'user_id' => Yii::t('app', 'User Id'),
            'channel_id' => Yii::t('app', 'Channel Id'),
            'extra' => Yii::t('app', 'Extra'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChannel()
    {
        return $this->hasOne(TransactionChannel::class, ['id' => 'channel_id']);
    }

    /**
     * @inheritdoc
     * @return TransactionChannelExtraQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionChannelExtraQuery(get_called_class());
    }
}
