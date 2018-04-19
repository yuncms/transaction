<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\contracts;

/**
 * ChannelInterface declares basic interface all Channel clients should follow.
 */
interface ChannelInterface
{
    /**
     * @param string $id channel id.
     */
    public function setIdentity($id);

    /**
     * @return string channel id
     */
    public function getIdentity();

    /**
     * @return string channel name.
     */
    public function getName();

    /**
     * @param string $name channel name.
     */
    public function setName($name);

    /**
     * @return string channel title.
     */
    public function getTitle();

    /**
     * @param string $title channel title.
     */
    public function setTitle($title);
}