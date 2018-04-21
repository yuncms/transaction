<?php

namespace yuncms\transaction\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\caching\DbDependency;
use yii\caching\ChainedDependency;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yuncms\helpers\DateHelper;
use yuncms\helpers\ArrayHelper;
use yuncms\user\models\User;

/**
 * This is the model class for table "{{%transaction_balance_transfer}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $recipient_id
 * @property integer $status
 * @property string $amount
 * @property string $order_no
 * @property string $user_fee
 * @property string $user_balance_transaction_id
 * @property string $recipient_balance_transaction
 * @property string $description
 * @property string $metadata
 * @property integer $created_at
 *
 * @property User $user
 *
 * @property-read boolean $isAuthor 是否是作者
 * @property-read boolean $isDraft 是否草稿
 * @property-read boolean $isPublished 是否发布
 */
class TransactionBalanceTransfer extends ActiveRecord{

    //场景定义
    const SCENARIO_CREATE = 'create';//创建
    const SCENARIO_UPDATE = 'update';//更新
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
        return '{{%transaction_balance_transfer}}';
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
        $behaviors['user'] = [
            'class' => BlameableBehavior::className(),
            'attributes' => [
                ActiveRecord::EVENT_BEFORE_INSERT => ['user_id']
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
            [['user_id', 'recipient_id', 'amount', 'created_at'], 'required'],
            [['user_id', 'recipient_id', 'status', 'user_balance_transaction_id', 'recipient_balance_transaction', 'created_at'], 'integer'],
            [['amount', 'user_fee'], 'number'],
            [['metadata'], 'string'],
            [['order_no'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 60],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            // status rule
            ['status', 'default', 'value' => self::STATUS_REVIEW],
            ['status', 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_REVIEW, self::STATUS_REJECTED, self::STATUS_PUBLISHED]],        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yuncms/transaction', 'ID'),
            'user_id' => Yii::t('yuncms/transaction', 'User Id'),
            'recipient_id' => Yii::t('yuncms/transaction', 'Recipient ID'),
            'status' => Yii::t('yuncms/transaction', 'Status'),
            'amount' => Yii::t('yuncms/transaction', 'Amount'),
            'order_no' => Yii::t('yuncms/transaction', 'Order No'),
            'user_fee' => Yii::t('yuncms/transaction', 'User Fee'),
            'user_balance_transaction_id' => Yii::t('yuncms/transaction', 'User Balance Transaction ID'),
            'recipient_balance_transaction' => Yii::t('yuncms/transaction', 'Recipient Balance Transaction'),
            'description' => Yii::t('yuncms/transaction', 'Description'),
            'metadata' => Yii::t('yuncms/transaction', 'Metadata'),
            'created_at' => Yii::t('yuncms/transaction', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return TransactionBalanceTransferQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionBalanceTransferQuery(get_called_class());
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
//    public function afterFind()
//    {
//        parent::afterFind();
//        // ...custom code here...
//    }

    /**
     * @inheritdoc
     */
//    public function beforeSave($insert)
//    {
//        if (!parent::beforeSave($insert)) {
//            return false;
//        }
//
//        // ...custom code here...
//        return true;
//    }

    /**
     * @inheritdoc
     */
//    public function afterSave($insert, $changedAttributes)
//    {
//        parent::afterSave($insert, $changedAttributes);
//        Yii::$app->queue->push(new ScanTextJob([
//            'modelId' => $this->getPrimaryKey(),
//            'modelClass' => get_class($this),
//            'scenario' => $this->isNewRecord ? 'new' : 'edit',
//            'category'=>'',
//        ]));
//        // ...custom code here...
//    }

    /**
     * @inheritdoc
     */
//    public function beforeDelete()
//    {
//        if (!parent::beforeDelete()) {
//            return false;
//        }
//        // ...custom code here...
//        return true;
//    }

    /**
     * @inheritdoc
     */
//    public function afterDelete()
//    {
//        parent::afterDelete();
//
//        // ...custom code here...
//    }
}
