<?php

namespace common\modules\mail\models;

use common\enums\AppType;
use common\models\AppModel;
use common\modules\mail\{Mail};
use common\modules\user\models\User;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%mailing}}".
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read User $user
 */
final class TestMailing extends AppModel
{
    /**
     * ID пользователя получателя
     */
    public ?int $user_id = null;

    /**
     * Список получателей
     */
    public ?string $mails = null;

    /**
     * ID рассылки
     */
    public ?int $mailing_id = null;

    /**
     * Тема письма
     */
    public ?string $mail_subject = null;

    /**
     * Текст письма
     */
    public ?string $mail_text = null;

    /**
     * Количество отправок
     */
    public ?int $mailing_count = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['mailing_count', 'required'],
            [
                ['mails', 'user_id'],
                function () {
                    if (empty($this->user_id) && empty($this->mails)) {
                        $this->addError('mails', Yii::t('app', 'At least 1 of the field must be filled up properly'));
                        $this->addError('user_id', Yii::t('app', 'At least 1 of the field must be filled up properly'));
                    }
                },
                'skipOnEmpty' => false
            ],
            [['user_id', 'mailing_id', 'mailing_count'], 'integer'],
            [['mails', 'mail_subject'], 'string', 'max' => 255],
            ['mail_text', 'string'],
            [
                'mailing_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => Mailing::class,
                'targetAttribute' => ['mailing_id' => 'id']
            ],
            [
                'user_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'user_id' => Yii::t(Mail::MODULE_MESSAGES, 'User'),
            'mails' => Yii::t(Mail::MODULE_MESSAGES, 'Emails To'),
            'mailing_id' => Yii::t(Mail::MODULE_MESSAGES, 'Mailing'),
            'mail_subject' => Yii::t(Mail::MODULE_MESSAGES, 'Mail Subject'),
            'mail_text' => Yii::t(Mail::MODULE_MESSAGES, 'Mail Text'),
            'mailing_count' => Yii::t(Mail::MODULE_MESSAGES, 'Mailing Count'),
        ];
    }

    public function getUser(): ?User
    {
        return User::findOne($this->user_id);
    }

    /**
     * Отправка тестовой рассылки
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function send(): void
    {
        $app_type = AppType::Admin;
        // Заполнение массива получателей рассылки
        $mailList = [];
        if (!empty($this->user_id)) {
            /** @var User $user */
            $user = User::find()->where(['id' => $this->user_id])->with('email')->one();
            $mailList[] = $user->email->value;
        }

        if (!empty($this->mails)) {
            $mailList = array_merge($mailList, preg_split('/[\s,]+/', $this->mails));
        }

        if (!$this->mail_text && !$this->mail_subject) { // Отправка зарегистрированной рассылки
            $i = 1;
            while ($i <= $this->mailing_count) {
                Yii::$app->mail->sendMailing(mails: $mailList, mailing_or_id: $this->mailing_id, appType: $app_type);
                $i++;
            }
        } else {  //Отправка своей рассылки
            $template = new Template();
            $template->text = $this->mail_text;
            $template->pugHtml = $this->mail_text;
            $temp = 'temp' . Yii::$app->user->id;
            $template->saveFiles($temp);
            try {
                $i = 1;
                while ($i <= $this->mailing_count) {
                    Yii::$app->mail->sendMailing(
                        mails: $mailList,
                        appType: $app_type,
                        message: $temp,
                        subject: $this->mail_subject
                    );
                    $i++;
                }
            } catch (Exception $e) {
                $template::deleteFiles($temp);
                throw $e;
            }
            $template::deleteFiles($temp);
        }
    }
}