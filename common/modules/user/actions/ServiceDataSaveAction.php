<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\components\exceptions\ModelSaveException;
use OpenApi\Attributes as OA;
use Yii;

/**
 * Запись служебной информации по пользователю
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[OA\Post(
    path: '/user/service-data-save',
    operationId: 'service-data-save',
    description: 'Сохранение любых дополнительных данных',
    summary: 'Сохранение сервисных данных',
    security: [['bearerAuth' => []]],
    tags: ['user'],
)]
#[OA\RequestBody(content: [new OA\MediaType(mediaType: 'application/json')])]
#[JsonSuccess(content: [
    new OA\Property(property: 'message', type: 'string', example: 'Service data saved successfully')
])]
class ServiceDataSaveAction extends BaseAction
{
    /**
     * @throws ModelSaveException
     */
    final public function run(): array
    {
        $data = Yii::$app->request->rawBody;
        $userExt = Yii::$app->user->identity->userExt;
        $userExt->service_data = $data;
        if (!$userExt->save()) {
            throw new ModelSaveException($userExt);
        }
        return $this->controller->returnSuccess('Service data saved successfully');
    }
}