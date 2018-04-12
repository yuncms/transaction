<?php

namespace yuncms\trade\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[TradeCharges]].
 *
 * @see TransactionCharges
 */
class TransactionChargesQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TransactionCharges[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TransactionCharges|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
