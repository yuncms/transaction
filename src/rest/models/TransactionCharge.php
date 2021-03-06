<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\rest\models;

use yuncms\rest\models\User;

/**
 * Class TransactionCharge
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class TransactionCharge extends \yuncms\transaction\models\TransactionCharge
{
    /**
     * 字段定义
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'user_id',
            'isPaid',
            'isRefunded',
            'isReversed',
            'paid',
            'refunded',
            'reversed',
            'channel',
            'order_no',
            'currency',
            'subject',
            'body',
            'amount',
            'amount_refunded',
            'credential',
            'metadata',
            'failure_code',
            'failure_msg',
            'extra'
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['jsonAttributes']);
        return $behaviors;
    }

    public function rules()
    {
        $rules = parent::rules();
        unset($rules['jsonField']);
        return $rules;
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
}
