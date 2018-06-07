<?php

namespace yuncms\transaction\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yuncms\base\JsonObject;
use yuncms\behaviors\IpBehavior;
use yuncms\behaviors\JsonBehavior;
use yuncms\db\ActiveRecord;
use yuncms\helpers\ArrayHelper;
use yuncms\helpers\Json;
use yuncms\transaction\contracts\ChannelInterface;
use yuncms\user\models\User;
use yuncms\validators\JsonValidator;

/**
 * This is the model class for table "{{%transaction_charges}}".
 *
 * @property int $id 付款流水号
 * @property int $paid 是否支付成功
 * @property int $refunded 是否有退款
 * @property int $reversed 订单是否撤销
 * @property int $user_id 用户ID
 * @property string $channel 付款渠道
 * @property string $order_no 订单号
 * @property string $client_ip 用户IP
 * @property int $amount
 * @property int $amount_settle
 * @property string $currency 币种
 * @property string $subject 订单标题
 * @property string $body 订单内容
 * @property string $order_class 订单类
 * @property JsonObject $extra
 * @property int $time_paid 付款时间
 * @property int $time_expire 过期时间
 * @property int $time_settle 清算时间
 * @property string $transaction_no 交易平台流水号
 * @property int $amount_refunded 已经退款的钱数
 * @property string $failure_code  失败代码
 * @property string $failure_msg 失败消息
 * @property JsonObject $metadata
 * @property string $description 描述
 * @property array $credential 客户端交易凭证
 * @property int $created_at 创建时间
 *
 *
 * @property-read string outTradeNo 订单号
 *
 * @property TransactionRefund[] $refunds 管理的退款对象
 * @property \yuncms\transaction\contracts\ChannelInterface $channelObject
 * @property \yii\db\ActiveQuery $source
 * @property bool $isReversed 订单是否撤销
 * @property bool $isRefunded 是否有退款
 * @property bool $isPaid 付款是否成功
 * @property string $refund
 * @property \yii\db\ActiveQuery $order
 * @property User $user
 */
class TransactionCharge extends ActiveRecord
{
    //支付成功触发事件
    const EVENT_AFTER_SUCCEEDED = 'charge.succeeded';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%transaction_charges}}';
    }

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
            ],
            'client_id' => [
                'class' => IpBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['client_ip']
                ],
            ],
            'jsonAttributes' => [
                'class' => JsonBehavior::class,
                'attributes' => ['extra', 'credential', 'metadata'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'order_no', 'channel', 'amount', 'currency', 'subject', 'body'], 'required'],

            [['paid', 'refunded', 'reversed',], 'integer'],
            [['paid', 'refunded', 'reversed',], 'default', 'value' => 0],

            ['order_class', 'string'],
            [['user_id', 'time_paid'], 'integer'],

            [['amount', 'amount_refunded'], 'number'],

            [['channel'], 'string', 'max' => 50],
            [['order_no', 'failure_code', 'failure_msg', 'description'], 'string', 'max' => 255],
            [['client_ip'], 'string', 'max' => 45],
            [['currency'], 'string', 'max' => 3],
            [['subject'], 'string', 'max' => 32],
            [['body'], 'string', 'max' => 128],
            [['channel'], 'channelExists'],
            ['amount_refunded', 'default', 'value' => 0],
            [['transaction_no'], 'string', 'max' => 64],

            //支付凭证
            [['credential', 'metadata', 'extra'], JsonValidator::class],

            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],

            //最晚付款时间
            [['time_expire'], 'integer', 'min' => time() + 900],
            [['time_expire'], 'default', 'value' => time() + 86400],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yuncms', 'ID'),
            'paid' => Yii::t('yuncms/transaction', 'Paid'),
            'refunded' => Yii::t('yuncms/transaction', 'Refunded'),
            'reversed' => Yii::t('yuncms/transaction', 'Reversed'),
            'channel' => Yii::t('yuncms/transaction', 'Channel'),
            'order_no' => Yii::t('yuncms/transaction', 'Order No'),
            'client_ip' => Yii::t('yuncms/transaction', 'Client Ip'),
            'amount' => Yii::t('yuncms/transaction', 'Amount'),
            'amount_settle' => Yii::t('yuncms/transaction', 'Amount Settle'),
            'currency' => Yii::t('yuncms/transaction', 'Currency'),
            'subject' => Yii::t('yuncms/transaction', 'Subject'),
            'body' => Yii::t('yuncms/transaction', 'Body'),
            'time_paid' => Yii::t('yuncms/transaction', 'Time Paid'),
            'time_expire' => Yii::t('yuncms/transaction', 'Time Expire'),
            'time_settle' => Yii::t('yuncms/transaction', 'Time Settle'),
            'transaction_no' => Yii::t('yuncms/transaction', 'Transaction No'),
            'amount_refunded' => Yii::t('yuncms/transaction', 'Amount Refunded'),
            'failure_code' => Yii::t('yuncms/transaction', 'Failure Code'),
            'failure_msg' => Yii::t('yuncms/transaction', 'Failure Msg'),
            'metadata' => Yii::t('yuncms/transaction', 'Metadata'),
            'description' => Yii::t('yuncms/transaction', 'Description'),
            'created_at' => Yii::t('yuncms', 'Created At'),
        ];
    }

    /**
     * Validate channel exists
     */
    public function channelExists()
    {
        try {
            TransactionChannel::getChannelByIdentity($this->channel);
        } catch (InvalidConfigException $e) {
            $this->addError('channel', $e->getMessage());
        } catch (UnknownClassException $e) {
            $this->addError('channel', $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     * @return TransactionChargeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionChargeQuery(get_called_class());
    }

    /**
     * 是否已经付款
     * @return bool
     */
    public function getIsPaid()
    {
        return (bool)$this->paid;
    }

    /**
     * 是否有退款
     * @return bool
     */
    public function getIsRefunded()
    {
        return (bool)$this->refunded;
    }

    /**
     * 订单是否撤销
     * @return bool
     */
    public function getIsReversed()
    {
        return (bool)$this->reversed;
    }

    /**
     * 商户订单号
     * @return int
     */
    public function getOutTradeNo()
    {
        return $this->id;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * 获取退款
     * @return \yii\db\ActiveQuery
     */
    public function getRefunds()
    {
        return $this->hasMany(TransactionRefund::class, ['charge_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne($this->order_class, ['id' => 'order_no']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne($this->order_class, ['id' => 'order_no']);
    }

    /**
     * 设置已付款状态
     * @param string $transactionNo 支付渠道返回的交易流水号。
     * @return bool
     */
    public function setPaid($transactionNo)
    {
        if ($this->paid) {
            return true;
        }
        $paid = (bool)$this->updateAttributes(['transaction_no' => $transactionNo, 'time_paid' => time(), 'paid' => true]);
        if (!empty($this->order_class) && is_subclass_of($this->order_class, 'yuncms\transaction\contracts\OrderInterface')) {//回调订单模型
            Yii::info('Callback order model:' . $this->order_class, __METHOD__);
            call_user_func_array([$this->order_class, 'setPaid'], [$this->order_no, $this->id, $this->metadata]);
        }
        $this->trigger(self::EVENT_AFTER_SUCCEEDED);
        return $paid;
    }

    /**
     * 设置交易凭证
     * @param string $transactionNo 支付渠道返回的交易流水号。
     * @param array $credential 支付凭证
     * @return bool
     */
    public function setCredential($transactionNo, $credential)
    {
        return (bool)$this->updateAttributes(['transaction_no' => $transactionNo, 'credential' => Json::encode($credential)]);
    }

    /**
     * 设置支付错误
     * @param string $code
     * @param string $msg
     * @return bool
     */
    public function setFailure($code, $msg)
    {
        return (bool)$this->updateAttributes(['failure_code' => $code, 'failure_msg' => $msg]);
    }

    /**
     * 从交易里直接发起退款
     * @param string $description
     * @return bool
     */
    public function setRefund($description)
    {
        $refund = new TransactionRefund(['amount' => $this->amount, 'description' => $description]);
        if ($refund->save() && $this->setReversed()) {
            return true;
        }
        return false;
    }

    /**
     * 设置订单状态以撤销
     * @return bool
     */
    public function setReversed()
    {
        return (bool)$this->updateAttributes(['reversed' => true, 'credential' => null]);
    }

    /**
     * 关闭支付
     * @return TransactionCharge
     * @throws InvalidConfigException
     * @throws UnknownClassException
     */
    public function setClose()
    {
        return $this->getChannelObject()->close($this);
    }

    /**
     * 查询渠道
     * @return TransactionCharge
     * @throws InvalidConfigException
     * @throws UnknownClassException
     */
    public function queryChannel()
    {
        return $this->getChannelObject()->query($this);
    }

    /**
     * 获取渠道对象
     * @return ChannelInterface
     * @throws InvalidConfigException
     * @throws UnknownClassException
     */
    public function getChannelObject()
    {
        return TransactionChannel::getChannelByIdentity($this->channel);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if($insert){
            $this->id = $this->generateId();
        }
        return true;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //保存完成以后去支付平台下单
        if ($insert) {
            try {
                $channel = TransactionChannel::getChannelByIdentity($this->channel);
                $channel->charge($this);
            } catch (InvalidConfigException $e) {

            } catch (UnknownClassException $e) {
            }
        }
    }
}
