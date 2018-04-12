<?php

namespace yuncms\transaction\backend\models;

use Yii;
use yii\data\ActiveDataProvider;
use yuncms\transaction\models\TransactionChannel as TransactionChannelModel;
use yii\base\Model;

/**
 * TransactionChannel represents the model behind the search form about `yuncms\transaction\models\TransactionChannel`.
 */
class TransactionChannelSearch extends TransactionChannelModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['identity', 'name', 'className', 'extra'], 'safe'],
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
        $query = TransactionChannelModel::find();

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'identity', $this->identity])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'className', $this->className])
            ->andFilterWhere(['like', 'extra', $this->extra]);

        return $dataProvider;
    }
}
