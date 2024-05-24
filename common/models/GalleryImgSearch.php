<?php

namespace common\models;

use common\components\helpers\SearchQueryHelper;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GalleryImgSearch represents the model behind the search form of `common\models\GalleryImg`.
 */
final class GalleryImgSearch extends GalleryImg
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'gallery_id'], 'integer'],
            [['img', 'name', 'text', 'created_at', 'updated_at'], 'safe']
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
        $query = GalleryImg::find();

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
            'gallery_id' => $this->gallery_id,
        ]);

        $query->andFilterWhere(['like', 'img', $this->img])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'text', $this->text]);

        // date filtering helper
        SearchQueryHelper::filterDataRange(['created_at', 'updated_at'], $this, $query);

        return $dataProvider;
    }
}
