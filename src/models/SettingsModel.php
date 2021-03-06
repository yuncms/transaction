<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\models;


use Yii;
use yii\base\Model;

/**
 * 渠道配置基类
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class SettingsModel extends Model
{
    /** @var integer */
    public $timeout;

    /** @var string */
    public $identity;

    /** @var string */
    public $name;

    /** @var string */
    public $title;

    public $class;

    /**
     * @var string
     */
    public $noticeUrl;

    /**
     * @var string
     */
    public $refundUrl;

    /**
     * @var string
     */
    public $returnUrl;

    /** @var TransactionChannel */
    private $_channel;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['identity', 'name', 'title', 'class', 'noticeUrl', 'refundUrl', 'returnUrl'], 'string'],
            [['timeout'], 'number', 'max' => 30, 'min' => 1],
            [['timeout'], 'default', 'value' => 5],

            [['noticeUrl'], 'required'],

            'noticeUrlDefault' => ['noticeUrl', 'default', 'value' => '/transaction/response/notice'],
            'returnUrlDefault' => ['returnUrl', 'default', 'value' => '/transaction/response/callback'],
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
     * 删除换行
     * @param string $str
     * @return mixed
     */
    public function deleteCRLF($str)
    {
        return str_replace(["\r\n", "\n", "\r"], '', $str);
    }

    /**
     * 保存渠道配置
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            $this->_channel->configuration = $this->getAttributes();
            return $this->_channel->save();
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'timeout' => Yii::t('yuncms/transaction', 'Timeout'),
            'noticeUrl' => Yii::t('yuncms/transaction', 'Charge Notice Route'),
            'refundUrl' => Yii::t('yuncms/transaction', 'Refund Notice Route'),
            'returnUrl' => Yii::t('yuncms/transaction', 'Charge Return Route'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
