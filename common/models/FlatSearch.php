<?php

namespace common\models;

use common\components\helpers\SearchQueryHelper;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * FlatSearch represents the model behind the search form of `common\models\Flat`.
 */
final class FlatSearch extends Flat
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'access_api'], 'integer'],
            [['title', 'subtitle', 'description', 'flat_img', 'address', 'additional_name', 'additional_img', 'created_at', 'updated_at'], 'safe'],
            [['cost', 'floor'], 'number']
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
        $query = Flat::find();

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
            'cost' => $this->cost,
            'floor' => $this->floor,
            'access_api' => $this->access_api,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'subtitle', $this->subtitle])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'flat_img', $this->flat_img])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'additional_name', $this->additional_name])
            ->andFilterWhere(['like', 'additional_img', $this->additional_img]);

        // date filtering helper
        SearchQueryHelper::filterDataRange(['created_at', 'updated_at'], $this, $query);

        return $dataProvider;
    }
}
