<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\transaction\rest\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ServerErrorHttpException;
use yuncms\rest\Controller;
use yuncms\transaction\rest\models\TransactionBalanceBonus;
use yuncms\transaction\rest\models\TransactionBalanceTransaction;

/**
 * 余额操作控制器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class BalanceController extends Controller
{

    /**
     * 创建余额增送
     * @return TransactionBalanceBonus
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionBonus()
    {
        $model = new TransactionBalanceBonus();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if (($model->save()) != false) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        return $model;
    }

    /**
     * 获取钱包明细
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTransaction()
    {
        $query = TransactionBalanceTransaction::find()->with('user');
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                    'id' => SORT_ASC,
                ]
            ],
        ]);
    }
}