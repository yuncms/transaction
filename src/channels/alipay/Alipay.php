<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\alipay;

use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\RequestEvent;
use yuncms\helpers\ArrayHelper;
use yuncms\helpers\Json;
use yuncms\transaction\contracts\ChannelInterface;
use yuncms\transaction\Exception;
use yuncms\transaction\models\TransactionCharge;
use yuncms\transaction\traits\ChannelTrait;

/**
 * Class Alipay
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
abstract class Alipay extends Client implements ChannelInterface
{
    use ChannelTrait;

    const SIGNATURE_METHOD_RSA = 'RSA';
    const SIGNATURE_METHOD_RSA2 = 'RSA2';

    /**
     * @var integer
     */
    public $appId;

    /** @var string */
    public $pid;

    /** @var string */
    public $alipayAccount;

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
    public $signType = self::SIGNATURE_METHOD_RSA2;

    /**
     * @var string 网关地址
     */
    //public $baseUrl = 'https://openapi.alipay.com';
    public $baseUrl = 'https://openapi.alipaydev.com';

    /**
     * 初始化
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!in_array('sha256', openssl_get_md_methods(), true)) {
            trigger_error('need openssl support sha256', E_USER_ERROR);
        }
        if (empty ($this->appId)) {
            throw new InvalidConfigException ('The "appId" property must be set.');
        }
        if (empty ($this->privateKey)) {
            throw new InvalidConfigException ('The "privateKey" property must be set.');
        }
        if (empty ($this->publicKey)) {
            throw new InvalidConfigException ('The "publicKey" property must be set.');
        }
        $this->initPrivateKey();
        $this->initPublicKey();
        $this->responseConfig['format'] = Client::FORMAT_JSON;
        $this->on(Client::EVENT_BEFORE_SEND, [$this, 'RequestEvent']);
        $this->on(Client::EVENT_AFTER_SEND, [$this, 'ResponseEvent']);
    }

    /**
     * 初始化私钥
     * @throws InvalidConfigException
     */
    protected function initPrivateKey()
    {
        $privateKey = Yii::getAlias($this->privateKey);
        if (is_file($privateKey)) {
            $privateKey = "file://" . $privateKey;
        } else {
            $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($this->privateKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        }
        $this->privateKey = openssl_pkey_get_private($privateKey);
        if ($this->privateKey === false) {
            throw new InvalidConfigException(openssl_error_string());
        }
    }

    /**
     * 初始化公钥
     * @throws InvalidConfigException
     */
    protected function initPublicKey()
    {
        $publicKey = Yii::getAlias($this->publicKey);
        if (is_file($publicKey)) {
            $publicKey = "file://" . $publicKey;
        } else {
            $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($this->publicKey, 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";
        }
        $this->publicKey = openssl_pkey_get_public($publicKey);
        if ($this->publicKey === false) {
            throw new InvalidConfigException(openssl_error_string());
        }
    }

    /**
     * 发送请求
     * @param string $method
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function sendRequest($method, $data = null, $headers = [], $options = [])
    {
        $request = $this->createRequest()
            ->setMethod($method)
            ->setHeaders($headers)
            ->setOptions($options)
            ->setUrl('gateway.do');
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
     * 包装付款参数
     * @param array $params
     * @return array
     */
    public function buildPaymentParameter($params = [])
    {
        $defaultParams = [
            'app_id' => $this->appId,
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => $this->signType,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->getNoticeUrl()
        ];
        $params = ArrayHelper::merge($defaultParams, $params);
        $params['biz_content'] = Json::encode($params['biz_content']);
        //签名
        if ($this->signType == self::SIGNATURE_METHOD_RSA2) {
            $params['sign'] = openssl_sign($this->getSignContent($params), $sign, $this->privateKey, OPENSSL_ALGO_SHA256) ? base64_encode($sign) : null;
        } elseif ($this->signType == self::SIGNATURE_METHOD_RSA) {
            $params['sign'] = openssl_sign($this->getSignContent($params), $sign, $this->privateKey, OPENSSL_ALGO_SHA1) ? base64_encode($sign) : null;
        }
        return $params;
    }

    /**
     * 请求事件
     * @param RequestEvent $event
     * @return void
     */
    public function RequestEvent(RequestEvent $event)
    {
        $params = $this->buildPaymentParameter($event->request->getData());
        $event->request->setData($params);
    }

    /**
     * 响应事件
     * @param RequestEvent $event
     * @throws Exception
     */
    public function ResponseEvent(RequestEvent $event)
    {
        if ($event->response->isOk) {
            $requestParams = $event->request->getData();
            $responseNode = str_replace('.', '_', $requestParams['method']) . '_response';
            if (!isset($event->response->data[$responseNode]) || !isset($event->response->data['sign'])) {
                throw new Exception('Parsing the response failed.');
            }
            if (($event->response->data = $this->verify($event->response->data[$responseNode], $event->response->data['sign'], true)) == false) {
                throw new Exception('Signature verification error.');
            }
        } else {
            throw new Exception ('Http request failed.');
        }
    }

    /**
     * 关闭订单
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function close(TransactionCharge $charge): TransactionCharge
    {
        // TODO: Implement close() method.
    }

    /**
     * 查询订单
     * @param TransactionCharge $charge
     * @return TransactionCharge
     */
    public function query(TransactionCharge $charge): TransactionCharge
    {
        // TODO: Implement query() method.
    }

    /**
     * 验证支付宝支付宝通知
     * @param array $data 通知数据
     * @param null $sign 数据签名
     * @param bool $sync
     * @return array|bool
     */
    public function verify($data, $sign = null, $sync = false)
    {
        $sign = is_null($sign) ? $data['sign'] : $sign;
        $toVerify = $sync ? json_encode($data) : $this->getSignContent($data, true);
        return openssl_verify($toVerify, base64_decode($sign), $this->publicKey, OPENSSL_ALGO_SHA256) === 1 ? $data : false;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return Yii::t('yuncms/transaction', 'Alipay');
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
     * 数据签名处理
     * @param array $toBeSigned
     * @param bool $verify
     * @return bool|string
     */
    protected function getSignContent(array $toBeSigned, $verify = false)
    {
        ksort($toBeSigned);
        $stringToBeSigned = '';
        foreach ($toBeSigned as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
        }
        $stringToBeSigned = substr($stringToBeSigned, 0, -1);
        unset($k, $v);
        return $stringToBeSigned;
    }

}