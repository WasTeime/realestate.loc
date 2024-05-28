<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\models\Docs;
use yii\helpers\ArrayHelper;
use OpenApi\Attributes as OA;

class DocsController extends AppController
{

    /**
     * {@inheritdoc}
     */
    public $modelClass = Docs::class;

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
        path: '/docs/index',
        operationId: 'docs-index',
        description: 'Возвращает список документов',
        summary: 'Список документов',
        security: [['bearerAuth' => []]],
        tags: ['docs']
    )]
    #[JsonSuccess(content: [
        new OA\Property(
            property: 'docs', type: 'array',
            items: new OA\Items('#/components/schemas/Docs'),
        )
    ])]
    public function actionIndex(): array
    {
        $texts = Docs::find()->all();
        return $this->returnSuccess($texts, 'docs');
    }
}
