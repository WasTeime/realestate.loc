<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonError, JsonSuccess};
use common\modules\user\{helpers\EmailHelper, models\User, Module};
use OpenApi\Attributes as OA;
use Yii;
use yii\base\{Exception, InvalidConfigException};

/**
 * Отправка письма для подтверждения почты
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[OA\Get(
    path: '/user/email-confirm-send',
    operationId: 'email-confirm-send',
    description: 'Запрос повторной отправки письма подтверждения',
    summary: 'Отправка письма подтверждения',
    security: [['bearerAuth' => []]],
    tags: ['user'],
)]
#[JsonSuccess(content: [new OA\Property(property: 'email', type: 'string', example: 'Message have been send')])]
#[JsonError(content: [
    new OA\Property(
        property: 'email', type: 'string', example: 'This e-mail is already confirmed'
    )
])]
class EmailConfirmSendAction extends BaseAction
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    final public function run(): array
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if ($user->email->is_confirmed) {
            return $this->controller->returnError(
                ['email' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'This e-mail is already confirmed')]
            );
        }
        EmailHelper::sendVerificationEmail($user->email, $user);
        return $this->controller->returnSuccess(
            ['email' => Yii::t(Module::MODULE_SUCCESS_MESSAGES, 'Message have been send')]
        );
    }
}