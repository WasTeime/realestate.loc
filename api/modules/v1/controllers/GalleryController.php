<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\components\Request;
use common\models\Gallery;
use yii\helpers\ArrayHelper;
use OpenApi\Attributes as OA;

class GalleryController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = Gallery::class;

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
        path: '/gallery/index',
        operationId: 'gallery-index',
        description: 'Возвращает галлерею и её изображения',
        summary: 'Галерея',
        security: [['bearerAuth' => []]],
        tags: ['gallery']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'id выбранной галлереи',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer')
    )]
    #[JsonSuccess(content: [
        new OA\Property(
            property: 'gallery', type: 'array',
            items: new OA\Items('#/components/schemas/Gallery'),
        )
    ])]
    public function actionIndex(Request $request): array
    {
        if ($request->get('id') == null) {
            return $this->returnSuccess(Gallery::find()->all(), 'gallery');
        }
        if (!Gallery::find()->where(['id' => $request->get('id')])->exists()) {
            return $this->returnError('no row in db', 'Галереи с таким id не существует');
        }
        return $this->returnSuccess(Gallery::findOne($request->get('id')), 'gallery');
    }
}
