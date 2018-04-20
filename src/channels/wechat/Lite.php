<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\wechat;

use Yii;
use yuncms\transaction\Exception;
use yuncms\transaction\models\TransactionCharge;

/**
 * 小程序付款
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class Lite extends Wechat
{

    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function charge(TransactionCharge $charge)
    {
        // TODO: Implement charge() method.
    }
}