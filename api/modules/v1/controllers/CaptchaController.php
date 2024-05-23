<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use OpenApi\Attributes as OA;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CaptchaController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @property array $methodsInfo
 */
final class CaptchaController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['get']]]);
    }

    /**
     * @param int $v Версия reCaptcha
     */
    #[OA\Get(
        path: '/captcha/get',
        operationId: 'get',
        description: 'Возвращает публичный ключ',
        summary: 'Получить ключ сайта для Google reCaptcha',
        tags: ['captcha']
    )]
    #[OA\Parameter(
        name: 'v',
        description: 'Версия reCaptcha, по умолчанию - 3',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer')
    )]
    #[JsonSuccess(
        response: 200,
        description: 'Публичный ключ',
        content: [new OA\Property(property: 'siteKey', type: 'string')]
    )]
    public function actionGet(int $v = 3): array
    {
        if ($v === 3) {
            return $this->returnSuccess(['siteKey' => Yii::$app->reCaptcha->siteKeyV3]);
        }
        if ($v === 2) {
            return $this->returnSuccess(['siteKey' => Yii::$app->reCaptcha->siteKeyV2]);
        }
        return $this->returnErrorBadRequest();
    }
}
