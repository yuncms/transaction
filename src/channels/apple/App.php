<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\apple;

/**
 * APP收款
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class App extends Apple
{
    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function charge(TransactionCharge $charge)
    {
        return $charge;
    }
}