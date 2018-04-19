<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\models;


use yii\base\Model;

class SettingsModel extends Model
{
    /** @var float */
    public $timeout;

    /** @var string */
    public $identity;

    /** @var string */
    public $name;

    /** @var string */
    public $title;

    public $class;

    /** @var TransactionChannel */
    private $_channel;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['identity', 'name', 'title','class'], 'string'],
        ];
    }

    /**
     * 设置渠道实例
     * @param TransactionChannel $channel
     */
    public function setChannel($channel)
    {
        $this->_channel = $channel;
        $this->setAttributes([
            'identity' => $channel->identity,
            'name' => $channel->name,
            'title' => $channel->title,
            'class' => $channel->className
        ]);
    }

    /**
     * 保存渠道配置
     * @return bool
     */
    public function save()
    {
        $this->_channel->configuration = $this->getAttributes();
        return $this->_channel->save();
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}