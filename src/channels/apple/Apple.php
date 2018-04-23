<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\apple;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\RequestEvent;
use yuncms\transaction\contracts\ChannelInterface;
use yuncms\transaction\models\TransactionCharge;
use yuncms\transaction\models\TransactionRefund;
use yuncms\transaction\traits\ChannelTrait;
use yuncms\web\Request;
use yuncms\web\Response;

/**
 * Class Apple
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
abstract class Apple extends Client implements ChannelInterface
{
    use ChannelTrait;

    /** @var string 沙盒地址 */
    protected $sandboxBaseUrl = 'https://sandbox.itunes.apple.com';

    /** @var string 正式地址i */
    protected $baseUrl = 'https://buy.itunes.apple.com';

    /** @var bool 是否使用沙盒 */
    public $sandbox = false;

    /** @var string 商户号 */
    public $merId;

    /**
     * @var array 查询方法
     */
    protected $methods = [
        'query' => 'verifyReceipt',
    ];

    /**
     * 初始化
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->merId)) {
            throw new InvalidConfigException ('The "merId" property must be set.');
        }
        if ($this->sandbox) {
            $this->baseUrl = $this->sandboxBaseUrl;
        }
        $this->requestConfig['format'] = Client::FORMAT_JSON;
        $this->responseConfig['format'] = Client::FORMAT_JSON;
        $this->on(Client::EVENT_BEFORE_SEND, [$this, 'RequestEvent']);
    }

    /**
     * 请求事件
     * @param RequestEvent $event
     * @return void
     */
    public function RequestEvent(RequestEvent $event)
    {
        $params = $event->request->getData();
        $event->request->setData($params);
    }

    /**
     * 获取设置模型
     * @return Model
     */
    public static function getSettingsModel()
    {
        return new SettingsModel();
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

    /**
     * 验证凭证
     * @param bool $verifySendbox 是否验证沙盒环境
     * @return bool
     */
    public function verifyReceipt()
    {
        if (strlen($this->receipt) < 10) {
            throw new Exception($response['return_msg']);
            $this->error = '凭证数据长度太短，请确定数据正确！';
            return false;
        }
        $return = $this->postData($this->receipt, $this->password, $this->baseUrl);
        if ($return) {
            $this->returnData = json_decode($return, true);
            if ($this->returnData['status'] != 0) {
                $this->setStatusError($this->returnData['status']);
                return false;
            }
            return $this->returnData;
        } else {
            $this->error = '与苹果服务器通讯失败！';
            return false;
        }
    }
}