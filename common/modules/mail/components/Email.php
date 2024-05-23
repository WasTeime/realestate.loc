<?php

namespace common\modules\mail\components;

use common\enums\AppType;
use common\models\Setting;
use common\modules\mail\{enums\LogStatus, enums\MailingType, models\Mailing, models\MailingLog};
use common\modules\user\models\User;
use Exception;
use Pug\Yii\ViewRenderer as PugViewRenderer;
use Yii;
use yii\base\{Component, InvalidConfigException};
use yii\helpers\{ArrayHelper, FileHelper, Json};
use yii\queue\Queue;
use yii\symfonymailer\Mailer;
use yii\web\NotFoundHttpException;

/**
 * Компонент для отправки почтовых рассылок и простых писем
 *
 * @package mail
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read array $mailerParams
 */
class Email extends Component
{
    /**
     * Событие вызываемое при инициализации отправки (до построения письма из шаблона)
     */
    public const EVENT_EMAIL_INIT = 'emailInit';

    /**
     * Событие вызываемое в момент перед отправкой
     */
    public const EVENT_EMAIL_BEFORE_SEND = 'emailBeforeSend';

    /**
     * Событие вызываемое после отправки
     */
    public const EVENT_EMAIL_AFTER_SEND = 'emailAfterSend';

    /**
     * Конфигурация SMTP подключения
     */
    private array $mailerData;

    /**
     * Параметры для класса Mailer
     */
    public array $params = [];

    /**
     * Использовать ли очереди Yii2 для отложенной отправки писем
     */
    public bool $useQueue = false;

    /**
     * Задержка в секундах между отправками
     */
    public int $delay = 2;

    /**
     * {@inheritdoc}
     * @throws NotFoundHttpException
     */
    public function init(): void
    {
        $this->initMailerConfig();
        parent::init();
    }

    /**
     * Отправка рассылки
     *
     * @param array|string $mails         Список адресов для отправки
     * @param null         $mailing_or_id Рассылка
     * @param array        $data          Данные в виде ассоциативного массива, БЕЗ объектов
     * @param AppType      $appType       Тип приложения
     * @param string|null  $message       Сообщение (название шаблона). Используется, если в контексте вызова невозможно получить mailing_or_id
     * @param string|null  $subject       Тема сообщения
     * @param int|null     $logId         ID предыдущего лога при повторении неудачной отправки
     * @param User|null    $user          Возможность указать пользователя вручную для использования его данных в письме и логе
     *                                    (Например когда надо отправить письмо на другой email)
     * @param bool         $isJob         Делать ли отправку через очередь
     *
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function sendMailing(
        array|string $mails,
        $mailing_or_id = null,
        array $data = [],
        AppType $appType = AppType::Undefined,
        string $message = null,
        string $subject = null,
        int $logId = null,
        User $user = null,
        bool $isJob = false
    ): void {
        $this->_checkConfiguration($mailing_or_id, $message, $subject, $mails, $to, $mailing_id);

        // Default view variables
        $data['domain'] = Yii::$app->request->hostInfo;

        if ($isJob && $this->useQueue && class_exists(Queue::class)) {
            $nextMailTime = max((int)Yii::$app->cache->get('lastMailTime') + $this->delay, time());
            $delay = $nextMailTime - time();
            Yii::$app->cache->set('lastMailTime', $nextMailTime);
            Yii::$app->queue
                ->delay(max($delay, 0))
                ->push(
                    new EmailJob([
                        'mails' => $mails,
                        'mailing_id' => $mailing_id,
                        'data' => $data,
                        'app_type' => $appType,
                        'message' => $message,
                        'subject' => $subject,
                        'log_id' => $logId,
                        'user_id' => $user->id ?? null,
                    ])
                );
            return;
        }

        foreach ($to as $mail) {
            try {
                Yii::debug('Mail try to send ' . $mail);
                if ($user === null) {
                    $user = User::findIdentityByEmail($mail);
                }
                Yii::$app->trigger(self::EVENT_EMAIL_BEFORE_SEND);
                if (!$this->sendMail($mail, $subject, $message, $user, $data)) {
                    throw new \yii\base\Exception('Ошибка отправки письма');
                }
                $user = null;
                $this->saveLog(
                    [
                        'mailing_id' => $mailing_id,
                        'mailing_subject' => $subject,
                        'mail_to' => $mail,
                        'status' => LogStatus::Success->value,
                        'app_type' => $appType->value
                    ],
                    'Успешно',
                    $logId,
                    $data
                );
                Yii::$app->trigger(self::EVENT_EMAIL_AFTER_SEND);
            } catch (Exception $e) {
                $this->saveLog(
                    [
                        'mailing_id' => $mailing_id,
                        'mailing_subject' => $subject,
                        'mail_to' => $mail,
                        'status' => LogStatus::Error->value,
                        'app_type' => $appType->value
                    ],
                    mb_substr($e->getMessage(), 0, 250),
                    $logId,
                    $data
                );
                throw $e;
            }
        }
    }

    /**
     * Проверка переданной конфигурации рассылки
     *
     * @throws InvalidConfigException
     */
    private function _checkConfiguration(
        int|Mailing|null $mailing_or_id,
        ?string &$message,
        ?string &$subject,
        array|string $mails,
        ?array &$to,
        ?int &$mailing_id
    ): void {
        if ($mailing_or_id === null && ($message === null || $subject === null)) {
            throw new InvalidConfigException(
                Yii::t('modules/mail/error', 'One of `mailing` or `message` with `subject` must be specified')
            );
        }
        Yii::$app->trigger(self::EVENT_EMAIL_INIT);
        if (is_numeric($mailing_or_id)) {
            /** @var Mailing|null $mailing */
            $mailing = Mailing::findOne($mailing_or_id);
        } else {
            $mailing = $mailing_or_id;
        }
        $to = [];
        if (is_array($mails)) {
            $to = $mails;
        } else {
            $to[] = $mails;
        }
        if ($mailing) {
            $message = $mailing->mailTemplate->name;
        } else {
            $mailing = Mailing::findOne(['name' => $message]);
        }
        $mailing_id = null;
        if ($mailing) {
            if ($mailing->mailing_type !== MailingType::Multiple->value && (count($to) > 1)) {
                throw new InvalidConfigException(
                    Yii::t('modules/mail/error', 'This mailing is personal. Please, choose one destination.')
                );
            }
            $mailing_id = (int)$mailing->id;
            if (!$subject) {
                $subject = (string)$mailing->mail_subject;
            }
        }
    }

    /**
     * Простая отправка письма
     *
     * @param string            $to      Адрес получателя
     * @param string            $subject Тема письма
     * @param array|string|null $message Шаблон письма
     * @param User|null         $user    Пользователь получатель
     * @param array|null        $data    Массив данных
     * @param array|string|null $from    Адрес отправителя
     *
     * @return array|bool Статус отправки, true если успешно
     *
     * @throws InvalidConfigException
     */
    public function sendMail(
        string $to,
        string $subject,
        array|string $message = null,
        User $user = null,
        array $data = null,
        array|string $from = null
    ): array|bool {
        if (!$message) {
            return ['error' => ['message' => 'An message template required.']];
        }

        if ($this->mailerData['host'] === 'smtp.test.ru') {
            throw new InvalidConfigException('Подключение к почтовому серверу не настроено');
        }

        if (is_string($from)) {
            $from = [$from];
        }
        if ($from === null) {
            if (!empty($this->mailerData['name_from'])) {
                $from = [$this->mailerData['from'] => $this->mailerData['name_from']];
            } else {
                $from = [$this->mailerData['from']];
            }
        }

        if (!$data) {
            $data = [];
        }

        // Set default username variable
        if (empty($data['username']) && $user) {
            $data['username'] = $user->userExt->first_name . ' ' . $user->userExt->last_name;
            if ($data['username'] === ' ') {
                $data['username'] = $user->username;
            }
        }

        /** @var Mailer $mailer */
        $mailer = Yii::createObject($this->mailerParams);
        $mailer->getView()->params['data'] = $data;

        if (is_string($message)) {
            $message = [
                'html_layout' => "$message-html.php",
                'text_layout' => "$message-text.php",
            ];
        }

        $send = $mailer->compose(
            ['html' => $message['html_layout'], 'text' => $message['text_layout']],
            ['user' => $user]
        );

        $status = $send->setTo($to)
            ->setFrom($from)
            ->setSubject($subject)
            ->send();

        $mailer->getView()->params['data'] = null;

        return $status;
    }

    /**
     * @throws NotFoundHttpException
     */
    private function initMailerConfig(): void
    {
        $this->mailerData['host'] = Setting::getParameterValue('email_server');
        $this->mailerData['username'] = Setting::getParameterValue('email_username');
        $this->mailerData['password'] = Setting::getParameterValue('email_password');
        $this->mailerData['port'] = Setting::getParameterValue('email_port');
        $this->mailerData['from'] = Setting::getParameterValue('email_from');
        $this->mailerData['name_from'] = Setting::getParameterValue('email_name_from');
    }

    public function getMailerParams(): array
    {
        return ArrayHelper::merge(
            [
                'class' => Mailer::class,
                'viewPath' => '@common/mail',
                'enableMailerLogging' => true,
                'useFileTransport' => YII_ENV_TEST,
                'transport' => [
                    'scheme' => 'smtp',
                    'host' => $this->mailerData['host'],
                    'username' => $this->mailerData['username'],
                    'password' => $this->mailerData['password'],
                    'port' => $this->mailerData['port'],
                    'options' => (int)($this->mailerData['port']) === 25
                        ? ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false]
                        : ['ssl' => true]
                ],
                'view' => ['renderers' => ['pug' => ['class' => PugViewRenderer::class]]]
            ],
            $this->params
        );
    }

    /**
     * Сохранение лога отправки
     *
     * @param array       $log         Данные лога
     * @param string|null $description Описание лога
     * @param int|null    $log_id      ID предыдущего лога
     * @param array       $data        Данные переданные в шаблон
     *
     * @throws InvalidConfigException
     */
    private function saveLog(
        array $log,
        string $description = null,
        int $log_id = null,
        array $data = []
    ): void {
        $model = new MailingLog();
        $model->load($log, '');
        $model->date = time();
        $model->data = Json::encode($data);
        if ($user = User::findIdentityByEmail($model->mail_to)) {
            $model->user_id = $user->id;
        }
        if ($description) {
            $model->description = mb_strimwidth($description, 0, 200, '...');
        }
        if ($log_id) {
            /** @var MailingLog $old_model */
            $old_model = MailingLog::find()->where(['id' => $log_id])->one();
            $old_model->status = LogStatus::Repeated->value;
            if ($old_model->save()) {
                $model->mailing_log_id = $log_id;
            }
        }
        if ($model->save()) {
            return;
        }

        Yii::warning(
            <<<HEREDOC
saveLog fail,
mailing_id=$model->mailing_id,
mailing_subject=$model->mailing_subject,
mail_to=$model->mail_to,
status=$model->status,
app_type=$model->app_type
HEREDOC
            ,
            __METHOD__
        );
    }

    /**
     * Получение списка шаблонов
     */
    public function getTemplates(bool $key_is_value = false): array
    {
        $templates = [];
        $files = FileHelper::findFiles(Yii::getAlias('@common/mail'));
        foreach ($files as $file) {
            $filename = basename($file);
            if (str_contains($filename, 'html.php')) {
                $val = explode('-html.php', $filename)[0];
                if ($key_is_value) {
                    $templates[$val] = $val;
                } else {
                    $templates[] = $val;
                }
            }
        }

        return $templates;
    }
}
