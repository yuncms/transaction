<?php

namespace yuncms\transaction\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yuncms\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%transaction_transfer}}".
 * 企业付款表，处理提现
 *
 * @property integer $id
 * @property string $type
 * @property string $channel
 * @property integer $status
 * @property string $order_no
 * @property string $amount
 * @property string $currency
 * @property string $recipient
 * @property string $description
 * @property string $transaction_no
 * @property string $failure_msg
 * @property string $metadata
 * @property string $extra
 * @property integer $created_at
 * @property integer $transferred_at
 *
 * @property-read boolean $isAuthor 是否是作者
 * @property-read boolean $isDraft 是否草稿
 * @property-read boolean $isPublished 是否发布
 */
class TransactionTransfer extends ActiveRecord
{

    //状态定义
    const STATUS_DRAFT = 0b0;//草稿
    const STATUS_REVIEW = 0b1;//待审核
    const STATUS_REJECTED = 0b10;//拒绝
    const STATUS_PUBLISHED = 0b11;//发布

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
        return '{{%transaction_transfer}}';
    }

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::className(),
            'attributes' => [
                ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
            ],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return ArrayHelper::merge($scenarios, [
            static::SCENARIO_CREATE => [],
            static::SCENARIO_UPDATE => [],
        ]);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'channel', 'order_no', 'amount', 'currency', 'recipient', 'description'], 'required'],
            [['status', 'created_at', 'transferred_at'], 'integer'],
            [['amount'], 'number'],
            [['metadata', 'extra'], 'string'],
            [['type'], 'string', 'max' => 5],
            [['channel', 'order_no', 'transaction_no'], 'string', 'max' => 64],
            [['currency'], 'string', 'max' => 3],
            [['recipient', 'description', 'failure_msg'], 'string', 'max' => 255],
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
            'type' => Yii::t('yuncms/transaction', 'Type'),
            'channel' => Yii::t('yuncms/transaction', 'Channel Identity'),
            'status' => Yii::t('yuncms/transaction', 'Status'),
            'order_no' => Yii::t('yuncms/transaction', 'Order No'),
            'amount' => Yii::t('yuncms/transaction', 'Amount'),
            'currency' => Yii::t('yuncms/transaction', 'Currency'),
            'recipient' => Yii::t('yuncms/transaction', 'Recipient'),
            'description' => Yii::t('yuncms/transaction', 'Description'),
            'transaction_no' => Yii::t('yuncms/transaction', 'Transaction No'),
            'failure_msg' => Yii::t('yuncms/transaction', 'Failure Msg'),
            'metadata' => Yii::t('yuncms/transaction', 'Metadata'),
            'extra' => Yii::t('yuncms/transaction', 'Extra'),
            'created_at' => Yii::t('yuncms/transaction', 'Created At'),
            'transferred_at' => Yii::t('yuncms/transaction', 'Transferred At'),
        ];
    }

    /**
     * @inheritdoc
     * @return TransactionTransferQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionTransferQuery(get_called_class());
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
     * 是否草稿状态
     * @return bool
     */
    public function isDraft()
    {
        return $this->status == static::STATUS_DRAFT;
    }

    /**
     * 是否发布状态
     * @return bool
     */
    public function isPublished()
    {
        return $this->status == static::STATUS_PUBLISHED;
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
