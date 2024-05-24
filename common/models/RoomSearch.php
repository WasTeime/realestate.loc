<?php

namespace common\models;

use common\components\helpers\SearchQueryHelper;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * RoomSearch represents the model behind the search form of `common\models\Room`.
 */
final class RoomSearch extends Room
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'flat_id'], 'integer'],
            [['name', 'uid', 'created_at', 'updated_at'], 'safe'],
            [['square'], 'number']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with a search query applied
     *
     * @throws InvalidConfigException
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Room::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'square' => $this->square,
            'flat_id' => $this->flat_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'uid', $this->uid]);

        // date filtering helper
        SearchQueryHelper::filterDataRange(['created_at', 'updated_at'], $this, $query);

        return $dataProvider;
    }
}
