<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\wechat;

use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\RequestEvent;
use yuncms\transaction\models\TransactionRefund;
use yuncms\web\Request;
use yuncms\transaction\Exception;
use yuncms\transaction\contracts\ChannelInterface;
use yuncms\transaction\models\TransactionCharge;
use yuncms\transaction\traits\ChannelTrait;
use yuncms\web\Response;

/**
 * Class Wechat
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 *
 * @property string $title
 */
abstract class Wechat extends Client implements ChannelInterface
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

    /** @var array 退款资金来源 */
    protected $refundAccount = [
        TransactionRefund::FUNDING_SOURCE_RECHARGE => 'REFUND_SOURCE_RECHARGE_FUNDS',//可用余额
        TransactionRefund::FUNDING_SOURCE_UNSETTLED => 'REFUND_SOURCE_UNSETTLED_FUNDS',//未结算资金
    ];

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
        $this->privateKey = Yii::getAlias($this->privateKey);
        if (!is_file($this->privateKey)) {
            throw new InvalidConfigException("Unable to read {$this->privateKey} file.");
        }
        if (empty ($this->publicKey)) {
            throw new InvalidConfigException ('The "publicKey" property must be set.');
        }
        $this->publicKey = Yii::getAlias($this->publicKey);
        if (!is_file($this->publicKey)) {
            throw new InvalidConfigException("Unable to read {$this->publicKey} file.");
        }

        $this->requestConfig['format'] = Client::FORMAT_XML;
        $this->requestConfig['options']['timeout'] = $this->timeout;
        $this->requestConfig['options']['sslCafile'] = __DIR__ . '/ca.pem';
        $this->requestConfig['options']['sslVerifyPeer'] = false;
        $this->requestConfig['options']['sslLocalCert'] = $this->publicKey;
        $this->requestConfig['options']['sslLocalPk'] = $this->privateKey;
        $this->responseConfig['format'] = Client::FORMAT_XML;
        $this->on(Client::EVENT_BEFORE_SEND, [$this, 'RequestEvent']);
    }

    /**
     * 请求事件
     * @param RequestEvent $event
     * @return void
     * @throws InvalidConfigException
     */
    public function RequestEvent(RequestEvent $event)
    {
        $params = $event->request->getData();
        $params['appid'] = $this->appId;
        $params['mch_id'] = $this->mchId;
        $params['nonce_str'] = $this->generateRandomString(32);
        $params['sign_type'] = $this->signType;
        $params['sign'] = $this->generateSignature($params);
        $event->request->setData($params);
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
     * @throws Exception
     */
    public function close(TransactionCharge $charge)
    {
        $response = $this->sendRequest('POST', 'pay/closeorder', [
            'out_trade_no' => $charge->outTradeNo,
        ]);
        if ($response['return_code'] == 'SUCCESS') {
            $charge->setReversed();
            return $charge;
        } else {
            throw new Exception($response['return_msg']);
        }
    }

    /**
     * 查询支付号
     * @param TransactionCharge $charge
     * @return TransactionCharge
     * @throws Exception
     */
    public function query(TransactionCharge $charge)
    {
        $response = $this->sendRequest('POST', 'pay/orderquery', [
            'out_trade_no' => $charge->outTradeNo,
        ]);
        if ($response['return_code'] == 'SUCCESS') {
            return $charge;
        } else {
            throw new Exception($response['return_msg']);
        }
    }

    /**
     * 退款请求
     * @param TransactionRefund $refund
     * @return TransactionRefund
     * @throws Exception
     */
    public function refund(TransactionRefund $refund)
    {
        $response = $this->sendRequest('POST', 'secapi/pay/refund', [
            'out_trade_no' => $refund->charge_id,
            'out_refund_no' => $refund->id,
            'refund_account' => $this->getRefundAccount($refund->funding_source),
            'total_fee' => bcmul($refund->charge->amount, 100),
            'refund_fee' => bcmul($refund->amount, 100),
        ]);
        if ($response['return_code'] == 'SUCCESS') {
            if ($response['result_code'] == 'SUCCESS') {
                $refund->setRefund($response['refund_id'], $response);
            } else {
                $refund->setFailure($response['err_code'], $response['err_code_des']);
            }
            return $refund;
        } else {
            throw new Exception($response['return_msg']);
        }
    }

    /**
     * 退款资金来源
     * @param string $fundingSource
     * @return mixed|string
     */
    public function getRefundAccount($fundingSource)
    {
        return isset($this->refundAccount[$fundingSource]) ? $this->refundAccount[$fundingSource] : 'REFUND_SOURCE_UNSETTLED_FUNDS';
    }

    /**
     * 发送请求
     * @param string $method
     * @param array|string $url
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function sendRequest($method, $url, $data = null, $headers = [], $options = [])
    {
        $request = $this->createRequest()
            ->setMethod($method)
            ->setHeaders($headers)
            ->addOptions($options)
            ->setUrl($url);
        if (is_array($data)) {
            $request->setData($data);
        } else {
            $request->setContent($data);
        }
        $response = $request->send();
        if ($response->isOk) {
            return $response->data;
        } else {
            throw new Exception ('Http request failed.');
        }
    }

    /**
     * 服务端通知
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function notice(Request $request, Response $response)
    {
        $response->format = Response::FORMAT_XML;
        $xml = $request->getRawBody();
        try {
            $params = $this->convertXmlToArray($xml);
            if ($params['return_code'] == 'SUCCESS' && $params['sign'] == $this->generateSignature($params)) {
                $response->content = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                $charge = $this->getChargeById($params['out_trade_no']);
                $charge->setPaid($params['transaction_id']);
            }
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __CLASS__);
        }
        $response->content = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[Signature verification failed.]]></return_msg></xml>';
    }

    /**
     * 退款服务端通知
     * @param Request $request
     * @param Response $response
     */
    public function refundNotice(Request $request, Response $response)
    {
        $response->format = Response::FORMAT_XML;
        $xml = $request->getRawBody();
        try {
            $params = $this->convertXmlToArray($xml);
            if ($params['return_code'] == 'SUCCESS') {
                $response->content = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            } else {

            }
            if ($params['return_code'] == 'SUCCESS' && $params['sign'] == $this->generateSignature($params)) {
                $response->content = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                //$refund = $this->getRefundById($params['out_refund_no']);
                //$refund->setPaid($params['transaction_id']);
            }
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __CLASS__);
        }
        $response->content = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[Signature verification failed.]]></return_msg></xml>';
    }

    /**
     * 支付响应
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function callback(Request $request, Response $response)
    {
        return;
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
