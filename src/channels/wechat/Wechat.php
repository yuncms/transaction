<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\wechat;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yuncms\web\Request;
use yuncms\base\HasHttpRequest;
use yuncms\transaction\contracts\ChannelInterface;
use yuncms\transaction\models\TransactionCharge;
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
    use HasHttpRequest;

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

    /**
     * 初始化
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->appId)) {
            throw new InvalidConfigException ('The "appId" property must be set.');
        }
        if (empty ($this->apiKey)) {
            throw new InvalidConfigException ('The "apiKey" property must be set.');
        }
        if (empty ($this->mchId)) {
            throw new InvalidConfigException ('The "mchId" property must be set.');
        }
        if (empty ($this->privateKey)) {
            throw new InvalidConfigException ('The "privateKey" property must be set.');
        }
        if (empty ($this->publicKey)) {
            throw new InvalidConfigException ('The "publicKey" property must be set.');
        }
    }

    /**
     * 获取设置模型
     * @return SettingsModel
     */
    public static function getSettingsModel()
    {
        return new SettingsModel();
    }


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return Yii::t('yuncms/transaction', 'Wechat');
    }

    /**
     * 关闭订单
     * @param TransactionCharge $charge
     * @return TransactionCharge
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function close(TransactionCharge $charge): TransactionCharge
    {
        $params = $this->buildParams([
            'out_trade_no' => $charge->id,
        ]);
        $response = $this->post('pay/closeorder', $params);
        return $charge;
    }

    /**
     * 查询订单号
     * @param TransactionCharge $charge
     * @return TransactionCharge
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function query(TransactionCharge $charge): TransactionCharge
    {
        $params = $this->buildParams([
            'out_trade_no' => $charge->id,
        ]);
        $response = $this->post('pay/orderquery', $params);
        return $charge;
    }

    /**
     * 服务端通知
     * @param Request $request
     * @param string $tradeId
     * @param float $money
     * @param string $message
     * @param string $payId
     * @return mixed
     */
    public function notice(Request $request, &$tradeId, &$money, &$message, &$payId)
    {
        $xml = $request->getRawBody();
        //如果返回成功则验证签名
        try {
            $params = $this->convertXmlToArray($xml);
            $tradeId = $params['out_trade_no'];
            $money = $params['total_fee'];
            $message = $params['return_code'];
            $payId = $params['transaction_id'];
            if ($params['return_code'] == 'SUCCESS' && $params['sign'] == $this->generateSignature($params)) {
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                return true;
            }
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __CLASS__);
        }
        echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        return false;
    }

    /**
     * 支付响应
     * @param Request $request
     * @param string $paymentId
     * @param $money
     * @param $message
     * @param $payId
     * @return mixed
     */
    public function callback(Request $request, &$paymentId, &$money, &$message, &$payId)
    {
        return;
    }

    /**
     * 编译参数
     * @param array $params
     * @return array
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    protected function buildParams($params = [])
    {
        $params['appid'] = $this->appId;
        $params['mch_id'] = $this->mchId;
        $params['nonce_str'] = $this->generateRandomString(32);
        $params['sign_type'] = $this->signType;
        $params['sign'] = $this->generateSignature($params);
        return $params;
    }

    /**
     * 生成签名
     * @param array $params
     * @return string
     * @throws InvalidConfigException
     */
    protected function generateSignature(array $params)
    {
        $bizParameters = [];
        foreach ($params as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $bizParameters[$k] = $v;
            }
        }
        ksort($bizParameters);
        $bizString = urldecode(http_build_query($bizParameters) . '&key=' . $this->apiKey);
        if ($this->signType == self::SIGNATURE_METHOD_MD5) {
            $sign = md5($bizString);
        } elseif ($this->signType == self::SIGNATURE_METHOD_SHA256) {
            $sign = hash_hmac('sha256', $bizString, $this->apiKey);
        } else {
            throw new InvalidConfigException ('This encryption is not supported');
        }
        return strtoupper($sign);
    }

    /**
     * 转换XML到数组
     * @param \SimpleXMLElement|string $xml
     * @return array
     */
    protected function convertXmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}