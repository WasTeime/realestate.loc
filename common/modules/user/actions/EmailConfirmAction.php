<?php

namespace common\modules\user\actions;

use common\components\exceptions\ModelSaveException;
use common\modules\user\models\Email;
use Exception;
use OpenApi\Attributes as OA;
use Yii;
use yii\web\Response;

/**
 * Подтверждение почты пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[OA\Get(
    path: '/user/email-confirm',
    operationId: 'email-confirm',
    description: 'Подтверждение почты токеном из письма',
    summary: 'Подтверждение почты',
    tags: ['user'],
)]
#[OA\Response(response: 302, description: 'Redirect to frontend page with "confirm_status" parameter')]
class EmailConfirmAction extends BaseAction
{
    /**
     * @throws ModelSaveException
     * @throws Exception
     */
    final public function run(
        #[OA\Parameter(name: 'token', description: 'Токен из письма подтверждения',
            in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
        string $token
    ): Response {
        $redirect_url = Yii::$app->request->hostInfo . '/?confirm_status=';
        $result = Email::confirm($token);
        if (isset($result['error'])) {
            return $this->controller->redirect($redirect_url . $result['error']);
        }
        return $this->controller->redirect($redirect_url . 'success');
    }
}