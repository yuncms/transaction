<?php

namespace yuncms\transaction\backend\models;

use Yii;
use yii\data\ActiveDataProvider;
use yuncms\transaction\models\TransactionCharge;
use yii\base\Model;

/**
 * TransactionChargeSearch represents the model behind the search form about `yuncms\transaction\models\TransactionCharge`.
 */
class TransactionChargeSearch extends TransactionCharge
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['paid', 'refunded', 'reversed',], 'boolean'],
            [['id', 'channel', 'order_no', 'currency', 'subject', 'body', 'client_ip', 'transaction_no', 'failure_code', 'failure_msg', 'metadata', 'description', 'time_paid', 'time_expire', 'created_at'], 'safe'],
            [['amount', 'amount_refunded'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TransactionCharge::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'amount' => $this->amount,
            'amount_refunded' => $this->amount_refunded,
            'paid' => $this->paid,
            'refunded' => $this->refunded,
            'reversed' => $this->reversed,
            'channel' => $this->channel,
            'order_no' => $this->order_no,
            'transaction_no' => $this->transaction_no,
            'currency' => $this->currency,
            'client_ip' => $this->client_ip,
        ]);

        if (!empty($this->time_expire)) {
            $date = strtotime($this->time_expire);
            $query->andWhere(['between', 'time_expire', $date, $date + 3600 * 24]);
        }
        if (!empty($this->time_paid)) {
            $date = strtotime($this->time_paid);
            $query->andWhere(['between', 'time_paid', $date, $date + 3600 * 24]);
        }
        if (!empty($this->created_at)) {
            $date = strtotime($this->created_at);
            $query->andWhere(['between', 'created_at', $date, $date + 3600 * 24]);
        }

        $query->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'body', $this->body])
            ->andFilterWhere(['like', 'failure_code', $this->failure_code])
            ->andFilterWhere(['like', 'failure_msg', $this->failure_msg]);

        return $dataProvider;
    }
}
