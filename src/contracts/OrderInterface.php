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
     * 设置订单付款成功
     * @param string $orderNo 订单号
     * @param string $chargeId 支付号
     * @param array $params 附加参数
     * @return bool
     */
    public static function setPaid($orderNo, $chargeId, $params);

    /**
     * 设置订单退款成功
     * @param string $orderNo 订单号
     * @param string $chargeId 支付号
     * @param array $params 附加参数
     * @return bool
     */
    public static function setRefunded($orderNo, $chargeId, $params);

}
