<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\jobs;


use yii\base\BaseObject;
use yii\httpclient\Client;
use yii\queue\RetryableJobInterface;

class NoticeCallBack extends BaseObject implements RetryableJobInterface
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $transactionId;
    public $chargeId;

    /**
     * 下载头像并保存
     * @param \yii\queue\Queue $queue
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function execute($queue)
    {
        $client = new Client();
        $client->post($this->url, ['charge_id' => $this->chargeId, 'transaction_id' => $this->transactionId])->send();
    }

    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return 60;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}
