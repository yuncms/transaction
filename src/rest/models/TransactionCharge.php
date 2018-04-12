<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\rest\models;

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
            'channel',
            'order_no',
            'currency',
            'subject',
            'body',
            'amount',
            'amount_refunded',
            'refunds'
        ];
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