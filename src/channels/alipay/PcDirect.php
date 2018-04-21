<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\alipay;
use yuncms\transaction\models\TransactionCharge;

/**
 * 支付宝电脑版支付
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class PcDirect extends Alipay
{

    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function charge(TransactionCharge $charge)
    {

    }
}