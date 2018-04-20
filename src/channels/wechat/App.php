<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\wechat;

use Yii;
use yuncms\transaction\models\TransactionCharge;

/**
 * Class App
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class App extends Wechat
{
    /**
     * 付款
     * @param TransactionCharge $charge
     * @return mixed
     */
    public function charge(TransactionCharge $charge)
    {
        $data = [
            'body' => $charge->body,
            'out_trade_no' => $charge->outTradeNo,
            'total_fee' => $charge->amount,
            'fee_type' => $charge->currency,
            'trade_type' => 'APP',
            //'notify_url' => $this->getNoticeUrl(),
            'spbill_create_ip' => Yii::$app->request->isConsoleRequest ? '127.0.0.1' : Yii::$app->request->userIP,
            'device_info' => 'WEB',
            'attach' => $charge->extra,
        ];
        $response = $this->post('pay/unifiedorder', $data);
        print_r($response);
    }
}