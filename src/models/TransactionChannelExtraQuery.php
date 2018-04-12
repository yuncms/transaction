<?php

namespace yuncms\transaction\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[TransactionChannelExtra]].
 *
 * @see TransactionChannelExtra
 */
class TransactionChannelExtraQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return TransactionChannelExtra[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TransactionChannelExtra|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
