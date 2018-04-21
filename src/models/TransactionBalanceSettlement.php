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

/**
 * This is the model class for table "{{%transaction_balance_settlement}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $amount
 * @property string $user_fee
 * @property integer $refunded
 * @property string $amount_refunded
 * @property string $charge_id
 * @property string $charge_order_no
 * @property string $charge_transaction_no
 * @property string $failure_msg
 * @property integer $created_at
 *
 * @property User $user
 *
 * @property-read boolean $isAuthor 是否是作者
 */
class TransactionBalanceSettlement extends \yii\db\ActiveRecord{

    //场景定义
    const SCENARIO_CREATE = 'create';//创建
    const SCENARIO_UPDATE = 'update';//更新

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transaction_balance_settlement}}';
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
            [['user_id', 'amount', 'charge_id'], 'required'],
            [['user_id', 'created_at'], 'integer'],
            [['amount', 'user_fee', 'amount_refunded'], 'number'],
            [['refunded'], 'string', 'max' => 1],
            [['charge_id'], 'string', 'max' => 50],
            [['charge_order_no', 'charge_transaction_no'], 'string', 'max' => 64],
            [['failure_msg'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
                    ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yuncms/transaction', 'ID'),
            'user_id' => Yii::t('yuncms/transaction', 'User Id'),
            'amount' => Yii::t('yuncms/transaction', 'Amount'),
            'user_fee' => Yii::t('yuncms/transaction', 'User Fee'),
            'refunded' => Yii::t('yuncms/transaction', 'Refunded'),
            'amount_refunded' => Yii::t('yuncms/transaction', 'Amount Refunded'),
            'charge_id' => Yii::t('yuncms/transaction', 'Charge ID'),
            'charge_order_no' => Yii::t('yuncms/transaction', 'Charge Order No'),
            'charge_transaction_no' => Yii::t('yuncms/transaction', 'Charge Transaction No'),
            'failure_msg' => Yii::t('yuncms/transaction', 'Failure Msg'),
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
     * @return TransactionBalanceSettlementQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionBalanceSettlementQuery(get_called_class());
    }

    /**
     * 是否是作者
     * @return bool
     */
    public function getIsAuthor()
    {
        return $this->user_id == Yii::$app->user->id;
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

    /**
     * 生成一个独一无二的标识
     */
    protected function generateSlug()
    {
        $result = sprintf("%u", crc32($this->id));
        $slug = '';
        while ($result > 0) {
            $s = $result % 62;
            if ($s > 35) {
                $s = chr($s + 61);
            } elseif ($s > 9 && $s <= 35) {
                $s = chr($s + 55);
            }
            $slug .= $s;
            $result = floor($result / 62);
        }
        //return date('YmdHis') . $slug;
        return $slug;
    }

    /**
     * 获取模型总数
     * @param null|int $duration 缓存时间
     * @return int get the model rows
     */
    public static function getTotal($duration = null)
    {
        $total = static::getDb()->cache(function (Connection $db) {
            return static::find()->count();
        }, $duration, new ChainedDependency([
            'dependencies' => new DbDependency(['db' => self::getDb(), 'sql' => 'SELECT MAX(id) FROM ' . self::tableName()])
        ]));
        return $total;
    }

    /**
     * 获取模型今日新增总数
     * @param null|int $duration 缓存时间
     * @return int
     */
    public static function getTodayTotal($duration = null)
    {
        $total = static::getDb()->cache(function (Connection $db) {
            return static::find()->where(['between', 'created_at', DateHelper::todayFirstSecond(), DateHelper::todayLastSecond()])->count();
        }, $duration, new ChainedDependency([
            'dependencies' => new DbDependency(['db' => self::getDb(), 'sql' => 'SELECT MAX(created_at) FROM ' . self::tableName()])
        ]));
        return $total;
    }
}
