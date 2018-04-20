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
 * 公众号付款
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class Pub extends Wechat
{

    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function charge(TransactionCharge $charge)
    {
        $response = $this->request('POST', 'pay/unifiedorder', [
            'body' => $charge->body,
            'out_trade_no' => $charge->outTradeNo,
            'total_fee' => $charge->amount,
            'fee_type' => $charge->currency,
            'trade_type' => 'APP',
            'notify_url' => $this->getNoticeUrl(),
            'spbill_create_ip' => Yii::$app->request->isConsoleRequest ? '127.0.0.1' : Yii::$app->request->userIP,
            'device_info' => 'WEB',
            'attach' => $charge->extra,
            'openid' => $charge->metadata['openid']///TODO 获取Openid
        ]);
        if ($response['return_code'] == 'SUCCESS') {
            $tradeParams = [
                'appId' => $this->appId,
                'timeStamp' => time(),
                'nonceStr' => $this->generateRandomString(32),
                'package' => 'prepay_id=' . $response['prepay_id'],
                'signType' => 'MD5',
            ];
            $tradeParams['paySign'] = $this->generateSignature($tradeParams);
            $charge->setTransactionCredential($response['prepay_id'], $tradeParams);
            return $charge;
        } else {
            throw new Exception($response['return_msg']);
        }
    }
}