<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\channels\apple;

use yii\httpclient\Client;
use yuncms\transaction\contracts\ChannelInterface;

/**
 * 苹果内购
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class InAppPurchase extends Client implements ChannelInterface
{
    /**
     * @var string 测试时使用
     */
    public $testBaseUrl = 'https://sandbox.itunes.apple.com/verifyReceipt';

    /**
     * @var array 苹果服务器返回的数据
     */
    private $returnData = [];

    /**
     * 查询数据是否有效
     * @param $productId
     * @param \Closure $successCallBack
     * @return bool
     */
    public function query($productId, \Closure $successCallBack)
    {
        if ($this->returnData) {
            if ($this->returnData['status'] == 0) {
                if ($productId == $this->returnData['receipt']['in_app'][0]['product_id']) {
                    return call_user_func_array($successCallBack, [
                        $this->getTransactionId(),
                        $this->returnData
                    ]);
                } else {
                    $this->error = '非法的苹果商店product_id，这个凭证有可能是伪造的！';
                    return false;
                }
            } else {
                $this->error = '苹果服务器返回订单状态不正确!';
                return false;
            }
        } else {
            $this->error = '无效的苹果服务器返回数据！';
            return false;
        }
    }

    /**
     * curl提交数据
     * @param $receipt_data
     * @param string $password
     * @param $url
     * @return mixed
     */
    private function postData($receipt_data, $password, $url)
    {
        $postData = ["receipt-data" => $receipt_data, 'password' => $password];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}