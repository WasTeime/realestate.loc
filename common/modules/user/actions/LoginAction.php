<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonError, JsonSuccess, RequestFormData};
use common\components\exceptions\ModelSaveException;
use common\models\LoginForm;
use common\modules\user\helpers\UserHelper;
use common\modules\user\Module;
use OpenApi\Attributes as OA;
use Throwable;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\db\StaleObjectException;
use yii\web\{HttpException, Response};

/**
 * Авторизация пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[OA\Post(
    path: '/user/login',
    operationId: 'login',
    description: 'Авторизация с помощью логина + пароль',
    summary: 'Авторизация',
    tags: ['user']
)]
#[RequestFormData(
    requiredProps: ['login', 'password'],
    properties: [
        new OA\Property(property: 'login', description: 'Имя пользователя или E-mail адрес', type: 'string'),
        new OA\Property(property: 'password', description: 'Пароль', type: 'string')
    ]
)]
#[JsonSuccess(content: [new OA\Property(property: 'profile', ref: '#/components/schemas/Profile')])]
#[JsonError(description: 'Login error',
    content: [
        new OA\Property(
            property: 'login', type: 'array',
            items: new OA\Items(type: 'string', example: 'Необходимо заполнить «Логин».')
        ),
        new OA\Property(
            property: 'password', type: 'array',
            items: new OA\Items(type: 'string', example: 'Неверный логин или пароль')
        )
    ]
)]
class LoginAction extends BaseAction
{
    /**
     * @throws Throwable
     * @throws ModelSaveException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    final public function run(): Response|array|string
    {
        $soc = Yii::$app->request->getParameter('soc');
        $code = Yii::$app->request->getParameter('code');
        $error = Yii::$app->request->getParameter('error');
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        //Если пользователь нажал на "Отмена" при авторизации через соц. сеть.
        if ($error) {
            return $this->controller->returnOpenerResponse(['error' => ['login:error' => $error]]);
        }
        //Если разрешена авторизация через соц. сети, проверяем переданный id соц. сети.
        if (($soc || $code) && $userModule->enableSocAuthorization === true) {
            return $this->socAuth('login');
        }
        return $this->emailLogin();
    }

    /**
     * Авторизация по e-mail
     *
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    private function emailLogin(): array
    {
        $form = new LoginForm();
        $form->load(Yii::$app->request->post(), '');
        if (!$form->login()) {
            return $this->controller->returnError('Login error', $form->errors);
        }
        return $this->controller->returnSuccess(UserHelper::getProfile($form->user), 'profile');
    }
}