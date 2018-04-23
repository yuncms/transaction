<?php

namespace yuncms\transaction\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yuncms\user\models\User;

/**
 * This is the model class for table "{{%transaction_withdrawals}}".
 * 提现处理
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property string $amount
 * @property string $channel
 * @property string $metadata
 * @property string $extra
 * @property integer $created_at
 * @property integer $canceled_at
 * @property integer $succeeded_at
 *
 * @property User $user
 */
class TransactionWithdrawal extends ActiveRecord
{
    //事件定义
    const BEFORE_PUBLISHED = 'beforePublished';
    const AFTER_PUBLISHED = 'afterPublished';
    const BEFORE_REJECTED = 'beforeRejected';
    const AFTER_REJECTED = 'afterRejected';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transaction_withdrawals}}';
    }

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'attributes' => [
                ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
            ],
        ];
        $behaviors['user'] = [
            'class' => BlameableBehavior::class,
            'attributes' => [
                ActiveRecord::EVENT_BEFORE_INSERT => ['user_id']
            ],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'amount', 'channel'], 'required'],
            [['user_id', 'status', 'created_at', 'canceled_at', 'succeeded_at'], 'integer'],
            [['amount'], 'number'],
            [['metadata', 'extra'], 'string'],
            [['channel'], 'string', 'max' => 64],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            // status rule
            ['status', 'default', 'value' => self::STATUS_REVIEW],
            ['status', 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_REVIEW, self::STATUS_REJECTED, self::STATUS_PUBLISHED]],];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yuncms/transaction', 'ID'),
            'user_id' => Yii::t('yuncms/transaction', 'User Id'),
            'status' => Yii::t('yuncms/transaction', 'Status'),
            'amount' => Yii::t('yuncms/transaction', 'Amount'),
            'channel' => Yii::t('yuncms/transaction', 'Channel'),
            'metadata' => Yii::t('yuncms/transaction', 'Metadata'),
            'extra' => Yii::t('yuncms/transaction', 'Extra'),
            'created_at' => Yii::t('yuncms/transaction', 'Created At'),
            'canceled_at' => Yii::t('yuncms/transaction', 'Updated At'),
            'succeeded_at' => Yii::t('yuncms/transaction', 'Updated At'),
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
     * @inheritdoc
     * @return TransactionWithdrawalQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionWithdrawalQuery(get_called_class());
    }

    /**
     * 是否是作者
     * @return bool
     */
    public function getIsAuthor()
    {
        return $this->user_id == Yii::$app->user->id;
    }

    /**
     * 审核通过
     * @return int
     */
    public function setPublished()
    {
        $this->trigger(self::BEFORE_PUBLISHED);
        $rows = $this->updateAttributes(['status' => static::STATUS_PUBLISHED, 'published_at' => time()]);
        $this->trigger(self::AFTER_PUBLISHED);
        return $rows;
    }

    /**
     * 拒绝通过
     * @param string $failedReason 拒绝原因
     * @return int
     */
    public function setRejected($failedReason)
    {
        $this->trigger(self::BEFORE_REJECTED);
        $rows = $this->updateAttributes(['status' => static::STATUS_REJECTED, 'failed_reason' => $failedReason]);
        $this->trigger(self::AFTER_REJECTED);
        return $rows;
    }

    /**
     * 获取状态列表
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT => Yii::t('yuncms/transaction', 'Draft'),
            self::STATUS_REVIEW => Yii::t('yuncms/transaction', 'Review'),
            self::STATUS_REJECTED => Yii::t('yuncms/transaction', 'Rejected'),
            self::STATUS_PUBLISHED => Yii::t('yuncms/transaction', 'Published'),
        ];
    }
}
