<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\wechat;

use Yii;
use yuncms\helpers\ArrayHelper;

/**
 * Class SettingsModel
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class SettingsModel extends \yuncms\transaction\models\SettingsModel
{
    /** @var string */
    public $appId;

    /** @var string */
    public $apiKey;

    /** @var string */
    public $mchId;

    /** @var string */
    public $privateKey;

    /** @var string */
    public $publicKey;

    /** @var string */
    public $signType;

    /**
     * @return array
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            'string' => [['appId', 'apiKey', 'mchId', 'privateKey', 'publicKey', 'signType'], 'string'],
            'required' => [['appId', 'apiKey', 'mchId', 'privateKey', 'publicKey', 'signType'], 'required'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'appId' => Yii::t('yuncms/transaction', 'App Id'),
            'apiKey' => Yii::t('yuncms/transaction', 'Api Key'),
            'mchId' => Yii::t('yuncms/transaction', 'MchId'),
            'privateKey' => Yii::t('yuncms/transaction', 'PrivateKey'),
            'publicKey' => Yii::t('yuncms/transaction', 'PublicKey'),
            'signType' => Yii::t('yuncms/transaction', 'Sign Type'),
        ]);
    }
}