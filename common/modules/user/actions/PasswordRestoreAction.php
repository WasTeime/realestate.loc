<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonError, JsonSuccess, RequestFormData};
use common\enums\AppType;
use common\modules\user\{enums\PasswordRestoreType, models\User, Module};
use Exception;
use OpenApi\Attributes as OA;
use Yii;

/**
 * Запрос на восстановление пароля пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[OA\Post(
    path: '/user/password-restore',
    operationId: 'password-restore',
    description: 'Восстановление пароля',
    summary: 'Восстановление пароля',
    tags: ['user']
)]
#[RequestFormData(properties: [new OA\Property(property: 'email', description: 'E-mail адрес', type: 'string')])]
#[JsonSuccess(content: [
    new OA\Property(property: 'mail', type: 'string', example: 'Password recovery email sent')
])]
#[JsonError(response: 503, content: [
    new OA\Property(property: 'email', type: 'string', example: 'User is not found')
])]
#[JsonError(response: 508, content: [
    new OA\Property(property: 'password_restore', type: 'string', example: 'Password restore is blocked')
])]
class PasswordRestoreAction extends BaseAction
{
    /**
     * Тип восстановления пароля
     */
    public PasswordRestoreType $type;

    /**
     * Шаблон письма с новым паролем
     */
    public string $passwordDirectTemplate;

    /**
     * Шаблон письма с токеном восстановления
     */
    public string $passwordTokenTemplate;

    /**
     * {@inheritdoc}
     */
    final public function init(): void
    {
        parent::init();
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $this->type = $userModule->passwordRestoreType;
        $this->passwordDirectTemplate = $userModule->passwordSendTemplate;
        $this->passwordTokenTemplate = $userModule->passwordTokenTemplate;
    }

    /**
     * @throws \yii\base\Exception
     */
    final public function run(): ?array
    {
        $email = Yii::$app->request->getParameter('email');
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!$userModule->enablePasswordRestore) {
            return $this->controller->returnError(
                ['password_restore' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'Password restore is blocked')],
                null,
                508
            );
        }
        $user = User::findIdentityByEmail($email);
        if (!$user) {
            return $this->controller->returnError(
                ['email' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'User is not found')],
                null,
                503
            );
        }
        $result = match ($this->type) {
            PasswordRestoreType::Directly => $this->restorePasswordDirectlyToEmail($user, $email),
            PasswordRestoreType::ViaToken => $this->restorePasswordViaToken($user, $email)
        };
        if ($result === true) {
            return $this->controller->returnSuccess(
                ['mail' => Yii::t(Module::MODULE_SUCCESS_MESSAGES, 'Password recovery email sent')]
            );
        }
        return $this->controller->returnError($result);
    }

    /**
     * Отправка нового пароля напрямую в письме
     *
     * @throws \yii\base\Exception
     */
    private function restorePasswordDirectlyToEmail(User $user, string $email): bool|array
    {
        // создаём новый пароль
        $new_password = Yii::$app->security->generateRandomString(8);
        $user->setPassword($new_password);
        if (!$user->save(false)) {
            return $user->errors;
        }
        //Отправляем письмо с паролем
        $data['password'] = $new_password;
        try {
            Yii::$app->mail->sendMailing(
                mails:   $email,
                data:    $data,
                appType: AppType::Api,
                message: $this->passwordDirectTemplate,
                subject: Yii::t(Module::MODULE_MESSAGES, 'Password Recovery'),
                user:    $user
            );
        } catch (Exception $e) {
            return [
                'error' => YII_DEBUG
                    ? $e->getMessage()
                    : Yii::t(Module::MODULE_ERROR_MESSAGES, 'Message was not send')
            ];
        }
        return true;
    }

    /**
     * Отправка токена восстановления
     *
     * @throws \yii\base\Exception
     */
    private function restorePasswordViaToken(User $user, string $email): bool|array
    {
        $user->generatePasswordResetToken();
        if (!$user->save(false)) {
            return $user->errors;
        }
        try {
            Yii::$app->mail->sendMailing(
                mails:   $email,
                appType: AppType::Api,
                message: $this->passwordTokenTemplate,
                subject: Yii::t(Module::MODULE_MESSAGES, 'Password Recovery'),
                user:    $user
            );
        } catch (Exception $e) {
            return [
                'error' => YII_DEBUG
                    ? $e->getMessage()
                    : Yii::t(Module::MODULE_ERROR_MESSAGES, 'Message was not send')
            ];
        }
        return true;
    }
}