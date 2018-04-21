<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\alipay;

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
    /** @var string Appid */
    public $appId;

    /** @var string 合作者ID */
    public $pid;

    /** @var string 支付宝账号 */
    public $alipayAccount;

    /** @var string 加密方式 */
    public $signType;

    /** @var string 公钥 */
    public $publicKey;

    /** @var string 私钥 */
    public $privateKey;

    /**
     * @return array
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            'string' => [['appId', 'pid', 'alipayAccount', 'privateKey', 'publicKey', 'signType'], 'string'],
            'required' => [['appId', 'pid', 'alipayAccount', 'privateKey', 'publicKey', 'signType'], 'required'],
        ]);
    }

    /**
     * 验证前去除公钥私钥的换行
     * @return bool
     */
    public function beforeValidate()
    {
        $this->privateKey = $this->deleteCRLF($this->privateKey);
        $this->publicKey = $this->deleteCRLF($this->publicKey);
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'appId' => Yii::t('yuncms/transaction', 'App Id'),
            'pid' => Yii::t('yuncms/transaction', 'Alipay Pid'),
            'alipayAccount' => Yii::t('yuncms/transaction', 'Alipay Account'),
            'privateKey' => Yii::t('yuncms/transaction', 'PrivateKey'),
            'publicKey' => Yii::t('yuncms/transaction', 'Alipay PublicKey'),
            'signType' => Yii::t('yuncms/transaction', 'Sign Type'),

        ]);
    }
}