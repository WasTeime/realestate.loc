<?php

namespace common\modules\user\helpers;

use common\enums\AppType;
use common\modules\user\{models\Email, models\User, Module};
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;

/**
 * Class EmailHelper
 *
 * @package user
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class EmailHelper
{
    /**
     * Отправка письма для подтверждения почты
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public static function sendVerificationEmail(Email $email, IdentityInterface|User $user): void
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $message = $userModule->verificationEmailTemplate;
        $confirm_token = Yii::$app->security->generateRandomString();
        $email->confirm_token = $confirm_token;
        $email->save();
        $data['confirm_token'] = $confirm_token;
        $to = $email->value;
        $appType = AppType::fromAppId(Yii::$app->id);
        Yii::$app->mail->sendMailing(
            mails:   $to,
            data:    $data,
            appType: $appType,
            message: $message,
            subject: Yii::t(Module::MODULE_MESSAGES, 'Email confirmation'),
            user:    $user,
            isJob:   true
        );
    }
}