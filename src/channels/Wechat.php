<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels;

use yuncms\transaction\contracts\ChannelInterface;
use yuncms\transaction\traits\ChannelTrait;

/**
 * Class Wechat
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
abstract class Wechat implements ChannelInterface
{
    use ChannelTrait;
}