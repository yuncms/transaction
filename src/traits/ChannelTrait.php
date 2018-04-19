<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\traits;

use Yii;
use yii\helpers\Inflector;
use yuncms\helpers\StringHelper;

/**
 * Trait ChannelTrait
 * @package yuncms\transaction\traits
 */
trait ChannelTrait
{
    /**
     * @var float 连接超时时间
     */
    private $_timeout;

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
     * @param string $id service identity.
     */
    public function setIdentity($id)
    {
        $this->_identity = $id;
    }

    /**
     * @return string channel identity
     */
    public function getIdentity()
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
    public function getName()
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
    public function getTitle()
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
    protected function defaultName()
    {
        return Inflector::camel2id(StringHelper::basename(get_class($this)));
    }

    /**
     * Generates channel title.
     * @return string channel title.
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
        return $this->_timeout ?: 5.0;
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