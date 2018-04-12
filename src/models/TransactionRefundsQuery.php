<?php

namespace yuncms\trade\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[TradeRefunds]].
 *
 * @see TransactionRefunds
 */
class TransactionRefundsQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TransactionRefunds[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TransactionRefunds|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
