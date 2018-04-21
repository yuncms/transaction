<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\rest\models;

/**
 * Class TransactionRefund
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class TransactionRefund extends \yuncms\transaction\models\TransactionRefund
{
    /**
     * 获取付款单
     * @return \yii\db\ActiveQuery
     */
    public function getCharge()
    {
        return $this->hasOne(TransactionCharge::class, ['id' => 'charge_id']);
    }
}