<?php

namespace console\controllers;

use common\enums\AppType;
use common\modules\mail\models\Mailing;
use common\modules\user\models\Email;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\console\ExitCode;
use yii\web\NotFoundHttpException;

/**
 * Email Controller
 *
 * @package console\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class EmailController extends ConsoleController
{
    /**
     * Отправка готовой массовой рассылки
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionSendMailing(string $mailing): int
    {
        /* @var Email[] $emails */
        $emails = Email::find()->all();
        $mailingModel = Mailing::findOne(['name' => $mailing]);
        if ($mailingModel) {
            foreach ($emails as $email) {
                Yii::$app->mail->sendMailing(mails: $email->value, mailing_or_id: $mailingModel->id, appType: AppType::Admin);
            }
        }
        return ExitCode::OK;
    }
}