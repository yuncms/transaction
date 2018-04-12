<?php

namespace yuncms\transaction\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[TransactionChannel]].
 *
 * @see TransactionChannelSearch
 */
class TransactionChannelQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return TransactionChannel[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TransactionChannel|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
