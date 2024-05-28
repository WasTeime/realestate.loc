<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\models\Promocode;
use common\modules\user\enums\Status;
use common\modules\user\models\User;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use OpenApi\Attributes as OA;

class PromocodeController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = Promocode::class;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['index']]]);
    }

    /**
     * Returns a list of Text's
     */
    #[OA\Get(
        path: '/promocode/index',
        operationId: 'promocode-index',
        description: 'Промокоды',
        summary: 'Промокоды',
        security: [['bearerAuth' => []]],
        tags: ['promocode']
    )]
    #[JsonSuccess(content: [
        new OA\Property(
            property: 'promocode', type: 'array',
            items: new OA\Items('#/components/schemas/Promocode'),
        )
    ])]
    public function actionIndex(): array
    {
        $activeUsersWithoutPromos = User::find()->where(['status' => Status::Active->value])
            ->andWhere(['NOT IN', 'id', Promocode::find()->select(['user_id'])->distinct()])
            ->all();
        return $this->returnSuccess(User::find()->where(['NOT IN', User::tableName().'.id', Promocode::find()->select(['user_id'])->distinct()])->all());
        $promocodes = Promocode::find()->where(['user_id' => null])->all();

        for ($i = 0; $i < count($activeUsersWithoutPromos); $i++) {
            $j = rand(0, count($promocodes));
            print_r($promocodes[$j]);
            $promocodes[$j]->user_id = $activeUsersWithoutPromos[$i]->id;
            //$promocodes[$j]->save();
        }

        return $this->returnSuccess();
    }
}
