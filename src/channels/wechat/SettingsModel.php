<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\wechat;

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
            're' => [['appId', 'apiKey', 'mchId', 'privateKey', 'publicKey', 'signType'], 'required'],
        ]);
    }
}