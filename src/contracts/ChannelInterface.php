<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\contracts;

use yii\base\Model;
use yuncms\transaction\models\TransactionCharge;
use yuncms\transaction\models\TransactionRefund;

/**
 * ChannelInterface declares basic interface all Channel clients should follow.
 */
interface ChannelInterface
{
    /**
     * @param string $id channel id.
     */
    public function setIdentity($id);

    /**
     * @return string channel id
     */
    public function getIdentity(): string;

    /**
     * @return string channel name.
     */
    public function getName(): string;

    /**
     * @param string $name channel name.
     */
    public function setName($name);

    /**
     * @return string channel title.
     */
    public function getTitle(): string;

    /**
     * @param string $title channel title.
     */
    public function setTitle($title);

    /**
     * 获取设置模型
     * @return Model
     */
    public static function getSettingsModel();

    /**
     * 发起支付
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function charge(TransactionCharge $charge);

    /**
     * 关闭订单
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function close(TransactionCharge $charge);

    /**
     * 查询订单
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function query(TransactionCharge $charge);
}