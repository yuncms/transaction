<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\alipay;
use yuncms\transaction\models\TransactionCharge;
use yuncms\web\Request;
use yuncms\web\Response;

/**
 * Class App
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class App extends Alipay
{


    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     * @throws \yuncms\transaction\Exception
     */
    public function charge(TransactionCharge $charge)
    {
        $bizContent = [
            'out_trade_no' => $charge->outTradeNo,//商户订单号
            'total_amount' => $charge->amount,//订单总金额
            'subject' => $charge->subject,//订单标题
        ];
        $response = $this->sendRequest('POST',['method' => 'alipay.trade.app.pay', 'biz_content' => $bizContent]);
        print_r($response);


        exit;

        return $charge;
        return ['orderInfo' => http_build_query($params)];
    }

    /**
     * 支付回跳
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function callback(Request $request, Response $response)
    {
        // TODO: Implement callback() method.
    }

    /**
     * 服务端通知
     * @param Request $request 请求实例类
     * @param Response $response
     * @return mixed
     */
    public function notice(Request $request, Response $response)
    {
        // TODO: Implement notice() method.
    }
}