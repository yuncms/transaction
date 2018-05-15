<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\alipay;
use yuncms\helpers\ArrayHelper;
use yuncms\transaction\models\TransactionCharge;

/**
 * 手机支付
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class Wap extends Alipay
{

    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     * @throws \yii\base\InvalidConfigException
     */
    public function charge(TransactionCharge $charge)
    {
        $bizContent = [
            'out_trade_no' => $charge->outTradeNo,//商户订单号
            'total_amount' => $charge->amount,//订单总金额
            'subject' => $charge->subject,//订单标题
            'timeout_express' => (($charge->time_expire - time()) / 60) . 'm',
            'product_code' => 'QUICK_WAP_WAY',
        ];
        $bizContent = ArrayHelper::merge($bizContent, $charge->extra->toArray());
        $tradeParams = $this->buildRequestParameter(['method' => 'alipay.trade.app.pay', 'biz_content' => $bizContent]);

        $charge->setCredential(null, [
            'isShowPayLoading' => true,
            'payInfo' => http_build_query($tradeParams)
        ]);
        return $charge;
    }
}
