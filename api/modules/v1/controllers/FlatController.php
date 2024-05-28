<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\components\Request;
use common\models\Flat;
use Yii;
use yii\helpers\ArrayHelper;
use OpenApi\Attributes as OA;

class FlatController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = Flat::class;

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
        path: '/flat/index',
        operationId: 'flat-index',
        description: 'Возвращает квартиру и список её комнат',
        summary: 'Квартира',
        security: [['bearerAuth' => []]],
        tags: ['flat']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'id выбранной квартиры',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer')
    )]
    #[JsonSuccess(content: [
        new OA\Property(
            property: 'flats', type: 'array',
            items: new OA\Items('#/components/schemas/Flat'),
        )
    ])]
    public function actionIndex(Request $request): array
    {
        if ($request->get('id') == null) {
            return $this->returnSuccess(Flat::find()->all(), 'flats');
        }
        if (!Flat::find()->where(['id' => $request->get('id')])->exists()) {
            return $this->returnError('no row in db', 'Квартиры с таким id не существует');
        }
        return $this->returnSuccess(Flat::findOne($request->get('id')), 'flats');
    }
}
