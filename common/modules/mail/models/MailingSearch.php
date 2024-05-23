<?php

namespace common\modules\mail\models;

use common\modules\mail\enums\MailingType;
use yii\base\{InvalidConfigException, Model};
use yii\data\ActiveDataProvider;

/**
 * MailingSearch represents the model behind the search form of `app\modules\mail\models\Mailing`.
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class MailingSearch extends Mailing
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'mailing_type', 'mail_template_id'], 'integer'],
            MailingType::validator('mailing_type'),
            [['name', 'mail_subject', 'comment'], 'safe'],
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
        $query = Mailing::find();

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
            'mailing_type' => $this->mailing_type,
            'mail_template_id' => $this->mail_template_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'mail_subject', $this->mail_subject])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
