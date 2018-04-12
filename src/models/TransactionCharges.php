<?php

namespace yuncms\trade\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yuncms\behaviors\IpBehavior;
use yuncms\db\ActiveRecord;
use yuncms\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%trade_charges}}".
 *
 * @property int $id
 * @property int $paid
 * @property int $refunded
 * @property int $reversed
 * @property string $channel
 * @property string $order_no
 * @property string $client_ip
 * @property int $amount
 * @property int $amount_settle
 * @property string $currency
 * @property string $subject
 * @property string $body
 * @property int $time_paid
 * @property int $time_expire
 * @property int $time_settle
 * @property string $transaction_no
 * @property int $amount_refunded
 * @property string $failure_code
 * @property string $failure_msg
 * @property string $metadata
 * @property string $description
 * @property int $created_at
 */
class TransactionCharges extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trade_charges}}';
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
            'client_id' => [
                'class' => IpBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['client_ip']
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
            [['paid', 'refunded', 'reversed', 'amount', 'time_paid', 'time_expire', 'amount_refunded'], 'integer'],
            [['channel', 'order_no', 'amount', 'currency', 'subject', 'body'], 'required'],
            [['metadata'], 'string'],
            [['channel'], 'string', 'max' => 50],
            [['order_no', 'failure_code', 'failure_msg', 'description'], 'string', 'max' => 255],
            [['client_ip'], 'string', 'max' => 45],
            [['currency'], 'string', 'max' => 3],
            [['subject'], 'string', 'max' => 32],
            [['body'], 'string', 'max' => 128],
            ['amount_refunded', 'default', 'value' => 0],
            [['transaction_no'], 'string', 'max' => 64],
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
     * {@inheritdoc}
     * @return TransactionChargesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionChargesQuery(get_called_class());
    }

    /**
     * 生成交易流水号
     * @return string
     */
    protected function generateId()
    {
        $i = rand(0, 9999);
        do {
            if (9999 == $i) {
                $i = 0;
            }
            $i++;
            $id = time() . str_pad($i, 4, '0', STR_PAD_LEFT);
            $row = (new Query())->from(static::tableName())->where(['id' => $id])->exists();
        } while ($row);
        return $id;
    }
}
