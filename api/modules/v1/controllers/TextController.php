<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\models\Text;
use OpenApi\Attributes as OA;
use yii\helpers\ArrayHelper;

/**
 * Class TextController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class TextController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = Text::class;

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
        path: '/text/index',
        operationId: 'text-index',
        description: 'Возвращает список текстов',
        summary: 'Список текстов',
        security: [['bearerAuth' => []]],
        tags: ['text']
    )]
    #[JsonSuccess(content: [
        new OA\Property(
            property: 'texts', type: 'array',
            items: new OA\Items('#/components/schemas/Text'),
        )
    ])]
    public function actionIndex(): array
    {
        $texts = Text::find()->all();
        return $this->returnSuccess($texts, 'texts');
    }
}
