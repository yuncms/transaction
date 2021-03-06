<?php

namespace yuncms\transaction\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yuncms\behaviors\JsonBehavior;
use yuncms\db\ActiveRecord;
use yuncms\helpers\ArrayHelper;
use yuncms\helpers\Json;
use yuncms\user\models\User;
use yuncms\validators\JsonValidator;

/**
 * This is the model class for table "{{%transaction_refunds}}".
 *
 * @property int $id 退款序号
 * @property int $amount 退款金额
 * @property int $status 状态值
 * @property int $time_succeed 退款成功时间
 * @property string $description 退款描述
 * @property string $failure_code 失败代码
 * @property string $failure_msg 失败文案
 * @property int $charge_id 付款id
 * @property string $charge_order_no 付款订单号
 * @property string $transaction_no 交易平台退款流水号
 * @property string $funding_source 退款资金来源
 * @property int $created_at 创建时间
 *
 * @property string $statusText 状态文案
 * @property string $fundingSourceText 退款失败文案
 * @property bool $succeed 退款是否成功
 * @property TransactionCharge $charge 付款对象
 */
class TransactionRefund extends ActiveRecord
{
    //退款成功触发
    const EVENT_AFTER_SUCCEEDED = 'refund.succeeded';

    //退款状态
    const STATUS_PENDING = 0b0;
    const STATUS_SUCCEEDED = 0b1;
    const STATUS_FAILED = 0b10;

    //退款资金来源
    const FUNDING_SOURCE_UNSETTLED = 'unsettled_funds';//使用未结算资金退款
    const FUNDING_SOURCE_RECHARGE = 'recharge_funds';//使用可用余额退款

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%transaction_refunds}}';
    }

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
            'id' => [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'id',
                ],
                'value' => function ($event) {
                    return $event->sender->generateId();
                }
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
            ],
            'jsonAttributes' => [
                'class' => JsonBehavior::class,
                'attributes' => ['extra', 'metadata'],
            ],
            'user' => [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'user_id',
                ],
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'description', 'charge_id'], 'required'],
            [['charge_id', 'user_id'], 'integer'],

            [['amount'], 'number', 'min' => 0.01],

            [['description'], 'string', 'max' => 255],

            //退款资金来源
            [['funding_source'], 'string'],
            ['funding_source', 'default', 'value' => self::FUNDING_SOURCE_UNSETTLED],
            ['funding_source', 'in', 'range' => [self::FUNDING_SOURCE_UNSETTLED, self::FUNDING_SOURCE_RECHARGE]],

            //付款单检测
            [['charge_id'], 'chargeValidate'],


            //附加信息
            [['metadata', 'extra'], JsonValidator::class],

            //退款状态
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_SUCCEEDED, self::STATUS_FAILED]],

            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * Validate charge
     */
    public function chargeValidate()
    {
        if (($charge = TransactionCharge::findOne(['id' => $this->charge_id])) != null) {
            if ($charge->isPaid) {
                if (bcsub($charge->amount, $charge->amount_refunded, 2) < $this->amount) {
                    $message = Yii::t('yuncms/transaction', 'Exceeded the maximum refund amount.');
                    $this->addError('amount', $message);
                } else {
                    $this->charge_order_no = $charge->order_no;
                }
            } else {
                $message = Yii::t('yuncms/transaction', "Change transaction has not yet been paid and cannot be refunded.");
                $this->addError('charge_id', $message);
            }
        } else {
            $message = Yii::t('yuncms/transaction', "Unknown charge '{charge}'", ['charge' => $this->charge_id]);
            $this->addError('charge_id', $message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yuncms', 'ID'),
            'amount' => Yii::t('yuncms/transaction', 'Amount'),
            'succeed' => Yii::t('yuncms/transaction', 'Succeed'),
            'status' => Yii::t('yuncms/transaction', 'Status'),
            'time_succeed' => Yii::t('yuncms/transaction', 'Time Succeed'),
            'description' => Yii::t('yuncms/transaction', 'Description'),
            'failure_code' => Yii::t('yuncms/transaction', 'Failure Code'),
            'failure_msg' => Yii::t('yuncms/transaction', 'Failure Msg'),
            'charge_id' => Yii::t('yuncms/transaction', 'Charge ID'),
            'charge_order_no' => Yii::t('yuncms/transaction', 'Charge Order No'),
            'transaction_no' => Yii::t('yuncms/transaction', 'Transaction No'),
            'funding_source' => Yii::t('yuncms/transaction', 'Funding Source'),
            'created_at' => Yii::t('yuncms', 'Created At'),
        ];
    }

    public function getFundingSourceText()
    {
        switch ($this->funding_source) {
            case self::FUNDING_SOURCE_UNSETTLED:
                $genderName = Yii::t('yuncms/transaction', 'Unsettled Funds');
                break;
            case self::FUNDING_SOURCE_RECHARGE:
                $genderName = Yii::t('yuncms/transaction', 'Recharge Funds');
                break;
            default:
                throw new \RuntimeException('Your database is not supported!');
        }
        return $genderName;
    }

    public function getStatusText()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                $genderName = Yii::t('yuncms/transaction', 'Pending');
                break;
            case self::STATUS_SUCCEEDED:
                $genderName = Yii::t('yuncms/transaction', 'Succeeded');
                break;
            case self::STATUS_FAILED:
                $genderName = Yii::t('yuncms/transaction', 'Failed');
                break;
            default:
                throw new \RuntimeException('Your database is not supported!');
        }
        return $genderName;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCharge()
    {
        return $this->hasOne(TransactionCharge::class, ['id' => 'charge_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * 退款是否成功
     * @return bool
     */
    public function getSucceed()
    {
        return $this->status == self::STATUS_SUCCEEDED;
    }

    /**
     * 设置退款错误
     * @param string $code
     * @param string $msg
     * @return bool
     */
    public function setFailure($code, $msg)
    {
        $succeed = (bool)$this->updateAttributes(['status' => self::STATUS_FAILED, 'failure_code' => $code, 'failure_msg' => $msg]);
        return $succeed;
    }

    /**
     * 设置退款成功
     * @param string $successTime
     * @param array $params
     * @return bool
     */
    public function setRefunded($successTime, $params = [])
    {
        if ($this->succeed) {
            return true;
        }
        $succeed = (bool)$this->updateAttributes(['status' => self::STATUS_SUCCEEDED, 'time_succeed' => $successTime, 'extra' => Json::encode($params)]);
        $this->charge->updateAttributes(['amount_refunded' => bcadd($this->charge->amount_refunded, $this->amount, 2)]);
        //回调订单模型
        if (!empty($this->charge->order_class) && is_subclass_of($this->charge->order_class, 'yuncms\transaction\contracts\OrderInterface')) {//回调订单模型
            Yii::info('Callback order model:' . $this->charge->order_class, __METHOD__);
            call_user_func_array([$this->charge->order_class, 'setRefunded'], [$this->charge->order_no, $this->charge->id, $this->charge->metadata]);
        }
        $this->trigger(self::EVENT_AFTER_SUCCEEDED);
        return $succeed;
    }

    /**
     * 设置退款凭证
     * @param string $transactionNo 支付渠道返回的交易流水号。
     * @param array $extra 退款凭证
     * @return bool
     */
    public function setRefund($transactionNo, $extra)
    {
        return (bool)$this->updateAttributes(['transaction_no' => $transactionNo, 'extra' => Json::encode($extra)]);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->charge->updateAttributes(['refunded' => true]);
            $this->charge->getChannelObject()->refund($this);
        }
    }

    /**
     * {@inheritdoc}
     * @return TransactionRefundQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionRefundQuery(get_called_class());
    }
}
