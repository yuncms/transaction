<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\traits;

use Yii;
use yii\base\Exception;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\web\NotFoundHttpException;
use yuncms\helpers\StringHelper;
use yuncms\transaction\models\TransactionCharge;
use yuncms\transaction\models\TransactionRefund;

/**
 * Trait ChannelTrait
 * @package yuncms\transaction\traits
 */
trait ChannelTrait
{
    /**
     * @var float 连接超时时间
     */
    public $timeout;

    /**
     * @var string channel identity.
     * This value mainly used as HTTP request parameter.
     */
    private $_identity;

    /**
     * @var string auth channel name.
     * This value may be used in database records, CSS files and so on.
     */
    private $_name;

    /**
     * @var string auth channel title to display in views.
     */
    private $_title;

    /**
     * @var string URL, which user will be redirected after authentication at the Payment provider web site.
     * Note: this should be absolute URL (with http:// or https:// leading).
     * By default current URL will be used.
     */
    private $_returnUrl;

    /**
     * @var string 后端通知地址
     */
    private $_noticeUrl;


    /**
     * @var string 后端退款通知地址
     */
    private $_refundUrl;

    /**
     * @param string $id service identity.
     */
    public function setIdentity($id)
    {
        $this->_identity = $id;
    }

    /**
     * @return string channel identity
     */
    public function getIdentity(): string
    {
        if (empty($this->_identity)) {
            $this->_identity = $this->getName();
        }
        return $this->_identity;
    }

    /**
     * @param string $name channel name.
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string channel name.
     */
    public function getName(): string
    {
        if ($this->_name === null) {
            $this->_name = $this->defaultName();
        }
        return $this->_name;
    }

    /**
     * @param string $title channel title.
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return string channel title.
     */
    public function getTitle(): string
    {
        if ($this->_title === null) {
            $this->_title = $this->defaultTitle();
        }
        return $this->_title;
    }

    /**
     * Generates channel name.
     * @return string channel name.
     */
    protected function defaultName(): string
    {
        return Inflector::camel2id(StringHelper::basename(get_class($this)));
    }

    /**
     * Generates channel title.
     * @return string channel title.
     */
    protected function defaultTitle(): string
    {
        return StringHelper::basename(get_class($this));
    }

    /**
     * Return timeout.
     *
     * @return int|mixed
     */
    public function getTimeout(): float
    {
        return $this->timeout ?: 5.0;
    }

    /**
     * Set timeout.
     *
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = floatval($timeout);
        return $this;
    }

    /**
     * @param string $returnUrl return URL
     */
    public function setReturnUrl($returnUrl)
    {
        $this->_returnUrl = $returnUrl;
    }

    /**
     * @return string return URL.
     */
    public function getReturnUrl()
    {
        if ($this->_returnUrl === null) {
            $this->_returnUrl = $this->defaultReturnUrl();
        }
        return Url::to([$this->_returnUrl, 'channel' => $this->getIdentity()], true);
    }

    /**
     * @param string $noticeUrl return URL
     */
    public function setNoticeUrl($noticeUrl)
    {
        $this->_noticeUrl = $noticeUrl;
    }

    /**
     * @return string return URL.
     */
    public function getNoticeUrl()
    {
        if ($this->_noticeUrl === null) {
            $this->_noticeUrl = $this->defaultNoticeUrl();
        }
        return Url::to([$this->_noticeUrl, 'channel' => $this->getIdentity()], true);
    }

    /**
     * @param string $noticeUrl return URL
     */
    public function setRefundUrl($noticeUrl)
    {
        $this->_noticeUrl = $noticeUrl;
    }

    /**
     * @return string return URL.
     */
    public function getRefundUrl()
    {
        if ($this->_refundUrl === null) {
            $this->_refundUrl = $this->defaultRefundUrl();
        }
        return Url::to([$this->_refundUrl, 'channel' => $this->getIdentity()], true);
    }

    /**
     * 获取BaseUri
     * @return string|null
     */
    public function getBaseUri()
    {
        if (property_exists($this, 'timeout')) {
            return $this->baseUrl;
        }
        return null;
    }

    /**
     * Composes URL from base URL and GET params.
     * @param string $url base URL.
     * @param array $params GET params.
     * @return string composed URL.
     */
    protected function composeUrl($url, array $params = [])
    {
        if (!empty($params)) {
            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }
        return $url;
    }


    /**
     * Composes default [[returnUrl]] value.
     * @return string return URL.
     */
    public function defaultReturnUrl()
    {
        return '/transaction/response/callback';
    }

    /**
     * Composes default [[noticeUrl]] value.
     * @return string return URL.
     */
    public function defaultNoticeUrl()
    {
        return '/transaction/response/notice';
    }

    /**
     * Composes default [[refundUrl]] value.
     * @return string return URL.
     */
    public function defaultRefundUrl()
    {
        return '/transaction/response/refund';
    }

    /**
     * 获取支付单
     * @param int $id
     * @return TransactionCharge
     * @throws NotFoundHttpException
     */
    public function getChargeById($id)
    {
        if (($model = TransactionCharge::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested charge does not exist.');
        }
    }

    /**
     * 获取退款单
     * @param int $id
     * @return TransactionRefund
     * @throws NotFoundHttpException
     */
    public function getRefundById($id)
    {
        if (($model = TransactionRefund::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested refund does not exist.');
        }
    }

    /**
     * 生成一个指定长度的随机字符串
     * @param int $length
     * @return string
     */
    protected function generateRandomString($length = 32): string
    {
        try {
            return Yii::$app->security->generateRandomString($length);
        } catch (Exception $e) {
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
            $randStr = str_shuffle($str);
            $rands = substr($randStr, 0, $length);
            return $rands;
        }
    }
}
