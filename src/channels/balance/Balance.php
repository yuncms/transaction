<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\balance;

use yii\base\BaseObject;
use yii\base\Model;
use yuncms\balance\models\BalanceTransaction;
use yuncms\transaction\contracts\ChannelInterface;
use yuncms\transaction\models\TransactionCharge;
use yuncms\transaction\models\TransactionRefund;
use yuncms\transaction\traits\ChannelTrait;
use yuncms\web\Request;
use yuncms\web\Response;

/**
 * Class Balance
 * @package yuncms\transaction\channels\balance
 */
class Balance extends BaseObject implements ChannelInterface
{
    use ChannelTrait;

    /**
     *
     */
    public function init()
    {
        parent::init();

    }

    /**
     * 获取设置模型
     * @return Model
     */
    public static function getSettingsModel()
    {
        // TODO: Implement getSettingsModel() method.
    }

    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     * @throws \yii\base\Exception
     */
    public function charge(TransactionCharge $charge)
    {
        //检查余额
        if ($charge->user->balance >= $charge->amount) {
            if (($transaction_id = \yuncms\balance\models\Balance::decrease($charge->user, $charge->amount, BalanceTransaction::TYPE_PAYMENT, $charge->body)) !== false) {
                //交易成功
                $charge->setPaid($transaction_id);
            } else {
                $charge->setFailure(1, '余额不足');
            }
        }
        return $charge;
    }

    /**
     * 关闭订单
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function close(TransactionCharge $charge)
    {
        return $charge;
    }

    /**
     * 查询订单
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function query(TransactionCharge $charge)
    {
        return $charge;
    }

    /**
     * 发起退款
     * @param TransactionRefund $refund
     * @return TransactionRefund
     */
    public function refund(TransactionRefund $refund)
    {
        return $refund;
    }

    /**
     * 支付回跳
     * @param Request $request
     * @param Response $response
     * @return TransactionCharge
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

    /**
     * 服务端退款通知
     * @param Request $request 请求实例类
     * @param Response $response
     * @return mixed
     */
    public function refundNotice(Request $request, Response $response)
    {
        // TODO: Implement refundNotice() method.
    }
}
