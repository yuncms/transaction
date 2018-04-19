<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels;

use Yii;
use yii\base\Component;
use yii\helpers\Inflector;
use yuncms\helpers\StringHelper;
use yuncms\transaction\contracts\ChannelInterface;

/**
 * Class Channel
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
abstract class Channel extends Component implements ChannelInterface
{
    //连接超时
    const DEFAULT_TIMEOUT = 5.0;

    /**
     * @var float 连接超时时间
     */
    private $_timeout;

    /**
     * @var string gateway service id.
     * This value mainly used as HTTP request parameter.
     */
    private $_id;

    /**
     * @var string auth service name.
     * This value may be used in database records, CSS files and so on.
     */
    private $_name;

    /**
     * @var string auth service title to display in views.
     */
    private $_title;

    /**
     * @param string $id service id.
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return string service id
     */
    public function getId()
    {
        if (empty($this->_id)) {
            $this->_id = $this->getName();
        }
        return $this->_id;
    }

    /**
     * @param string $name service name.
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string service name.
     */
    public function getName()
    {
        if ($this->_name === null) {
            $this->_name = $this->defaultName();
        }
        return $this->_name;
    }

    /**
     * @param string $title service title.
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return string service title.
     */
    public function getTitle()
    {
        if ($this->_title === null) {
            $this->_title = $this->defaultTitle();
        }
        return $this->_title;
    }

    /**
     * Generates service name.
     * @return string service name.
     */
    protected function defaultName()
    {
        return Inflector::camel2id(StringHelper::basename(get_class($this)));
    }

    /**
     * Generates service title.
     * @return string service title.
     */
    protected function defaultTitle()
    {
        return StringHelper::basename(get_class($this));
    }

    /**
     * Return timeout.
     *
     * @return int|mixed
     */
    public function getTimeout()
    {
        return $this->_timeout ?: self::DEFAULT_TIMEOUT;
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
        $this->_timeout = floatval($timeout);
        return $this;
    }

    public static function getI()
    {

    }

    /**
     * 生成一个指定长度的随机字符串
     * @param int $length
     * @return string
     * @throws \yii\base\Exception
     */
    protected function generateRandomString($length = 32)
    {
        return Yii::$app->security->generateRandomString($length);
    }
}