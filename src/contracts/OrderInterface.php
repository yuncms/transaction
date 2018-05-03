<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\contracts;


interface OrderInterface
{
    /**
     * 设置支付状态
     * @param string $orderNo 订单号
     * @param string $ChargeId 支付号
     * @param array $params 附加参数
     * @return bool
     */
    public static function setPaid($orderNo, $ChargeId, $params);
}
