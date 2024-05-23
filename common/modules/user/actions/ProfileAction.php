<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\modules\user\helpers\UserHelper;
use OpenApi\Attributes as OA;

/**
 * Возвращение профиля пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[OA\Post(
    path: '/user/profile',
    operationId: 'profile',
    description: 'Запрос данных профиля',
    summary: 'Данные профиля',
    security: [['bearerAuth' => []]],
    tags: ['user'],
)]
#[JsonSuccess(content: [new OA\Property(property: 'profile', ref: '#/components/schemas/Profile')])]
class ProfileAction extends BaseAction
{
    /**
     * @throws Exception
     * @throws HttpException
     */
    final public function run(): array
    {
        return $this->controller->returnSuccess(UserHelper::getProfile(), 'profile');
    }
}