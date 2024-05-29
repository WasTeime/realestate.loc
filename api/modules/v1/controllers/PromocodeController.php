<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonError;
use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\models\Promocode;
use common\modules\user\enums\Status;
use common\modules\user\models\User;
use Yii;
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
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        if ($promo = Promocode::find()->where(['user_id' => $user->id])->one()) {
            return $this->returnSuccess($promo, 'promocode');
        }
        $freePromo = Promocode::find()->where(['user_id' => null])->one();
        if ($freePromo != null) {
            $freePromo->user_id = $user->id;
            $freePromo->save();
            return $this->returnSuccess($freePromo, 'promocode');
        } else {
            return $this->returnError('Promocodes out', 'Все доступные промокоды закончились');
        }
    }
}
