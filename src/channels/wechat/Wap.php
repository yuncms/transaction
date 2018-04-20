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
 * 微信H5支付
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class Wap extends Wechat
{
    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     * @throws Exception
     */
    public function charge(TransactionCharge $charge)
    {
        $response = $this->sendRequest('POST', 'pay/unifiedorder', [
            'body' => $charge->body,
            'out_trade_no' => $charge->outTradeNo,
            'total_fee' => $charge->amount,
            'fee_type' => $charge->currency,
            'trade_type' => 'MWEB',
            'notify_url' => $this->getNoticeUrl(),
            'spbill_create_ip' => Yii::$app->request->isConsoleRequest ? '127.0.0.1' : Yii::$app->request->userIP,
            'device_info' => 'WEB',
            'attach' => $charge->extra,
        ]);
        if ($response['return_code'] == 'SUCCESS') {
            if ($response['result_code'] == 'SUCCESS') {
                $tradeParams = [
                    'mweb_url' => $response['mweb_url'],
                    'prepayid' => $response['prepay_id'],
                ];
                $charge->setTransactionCredential($response['prepay_id'], $tradeParams);
            } else {
                $charge->setFailure($response['err_code'], $response['err_code_des']);
            }
            return $charge;
        } else {
            throw new Exception($response['return_msg']);
        }
    }
}