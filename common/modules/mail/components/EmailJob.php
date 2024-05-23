<?php

namespace common\modules\mail\components;

use common\enums\AppType;
use common\modules\user\models\User;
use Yii;
use yii\base\{BaseObject, Exception, InvalidConfigException};
use yii\queue\JobInterface;
use yii\web\NotFoundHttpException;

/**
 * Работа для отложенной отправки email через queue
 *
 * @package mail
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class EmailJob extends BaseObject implements JobInterface
{
    /**
     * Список email адресов для отправки
     */
    public string|array $mails;

    /**
     * ID рассылки
     */
    public ?int $mailing_id;

    /**
     * Массив данных
     */
    public array $data = [];

    /**
     * Инициатор отправки
     */
    public AppType $app_type;

    /**
     * Название сообщения (шаблона)
     */
    public string|array|null $message;

    /**
     * Тема письма
     */
    public ?string $subject = null;

    /**
     * Номер лога отправки, которую пытаемся повторить
     */
    public ?int $log_id;

    /**
     * ID Пользователя получателя
     */
    public ?int $user_id;

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    final public function execute($queue): void
    {
        Yii::$app->set('mailer', Yii::$app->getComponents()['mailer']);
        Yii::$app->set('db', Yii::$app->getComponents()['db']);
        Yii::$app->mail->sendMailing(
            mails:         $this->mails,
            mailing_or_id: $this->mailing_id,
            data:          $this->data,
            appType:       $this->app_type,
            message:       $this->message,
            subject:       $this->subject,
            logId:         $this->log_id,
            user:          User::findOne($this->user_id)
        );
    }
}