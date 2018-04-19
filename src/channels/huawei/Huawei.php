<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\huawei;


use yii\base\BaseObject;
use yuncms\transaction\contracts\ChannelInterface;
use yuncms\transaction\traits\ChannelTrait;

abstract class Huawei extends BaseObject implements ChannelInterface
{
    use ChannelTrait;
}