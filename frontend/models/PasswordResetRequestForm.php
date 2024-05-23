<?php

namespace frontend\models;

use common\enums\AppType;
use common\models\AppModel;
use common\modules\mail\models\Mailing;
use common\modules\user\{enums\Status, models\Email, models\User};
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\NotFoundHttpException;

/**
 * Password reset request form
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class PasswordResetRequestForm extends AppModel
{
    /**
     * Email адрес
     */
    public ?string $email = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            [
                'email',
                'exist',
                'targetClass' => Email::class,
                'targetAttribute' => 'value',
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'email' => Yii::t('app', 'Email')
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was sent
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function sendEmail(): bool
    {
        /** @var User $user */
        $user = User::find()
            ->joinWith('email')
            ->where(['status' => Status::Active->value, 'value' => $this->email])
            ->one();

        if (!isset($user)) {
            $this->addError('email', 'Пользователь не найден');
            return false;
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                $this->addError('email', 'Ошибка генерации токена сброса пароля');
                return false;
            }
        }
        Yii::$app->mail->sendMailing(
            mails: $user->email->value,
            mailing_or_id: Mailing::find()->where(['name' => 'passwordResetToken'])->one(),
            appType: AppType::Frontend,
            user: $user,
            isJob: true
        );
        return true;
    }
}
