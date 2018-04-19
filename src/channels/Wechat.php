<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels;

use yii\base\BaseObject;
use yuncms\transaction\contracts\ChannelInterface;
use yuncms\transaction\traits\ChannelTrait;

/**
 * Class Wechat
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
abstract class Wechat extends BaseObject implements ChannelInterface
{
    use ChannelTrait;

    const SIGNATURE_METHOD_MD5 = 'MD5';
    const SIGNATURE_METHOD_SHA256 = 'HMAC-SHA256';

    /**
     * @var string 网关地址
     */
    public $baseUrl = 'https://api.mch.weixin.qq.com';
    /**
     * @var string 绑定支付的开放平台 APPID
     */
    public $appId;
    /**
     * @var string 商户支付密钥
     * @see https://pay.weixin.qq.com/index.php/core/cert/api_cert
     */
    public $apiKey;
    /**
     * @var string 商户号
     * @see https://pay.weixin.qq.com/index.php/core/account/info
     */
    public $mchId;
    /**
     * @var string 私钥
     */
    public $privateKey;
    /**
     * @var string 公钥
     */
    public $publicKey;
    /**
     * @var string 签名方法
     */
    public $signType = self::SIGNATURE_METHOD_SHA256;
}