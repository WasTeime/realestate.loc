<?php

namespace common\modules\mail\models;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MailTemplateSearch represents the model behind the search form of `admin\modules\mail\models\MailTemplate`.
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class MailTemplateSearch extends MailTemplate
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['id', 'integer'],
            ['name', 'safe'],
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
        $query = MailTemplate::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
