<?php

namespace common\modules\mail\models;

use common\enums\Boolean;
use common\models\AppActiveRecord;
use common\modules\mail\Mail;
use common\modules\user\enums\Status;
use common\modules\user\models\{Email, User, UserExt};
use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "{{%mail_template}}".
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int         $id   [int] ID
 * @property string      $name [varchar(255)] Название
 *
 * @property-read string $htmlTemplateFilename
 * @property-read string $textTemplateFilename
 */
class MailTemplate extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%mail_template}}';
    }

    /**
     * @throws Exception
     */
    public static function getDummyUser(): User
    {
        if (!$user = User::findOne(['username' => 'Username'])) {
            $user = new User();
            $user->id = 1;
            $user->username = 'Username';
            $user->status = Status::Active->value;
            $user->auth_source = 'admin-testing';
            $user->password_reset_token = Yii::$app->security->generateRandomString();
            $user->last_login_at = time();
        }
        if (!UserExt::findOne(['user_id' => $user->id])) {
            $userExt = new UserExt();
            $userExt->id = 1;
            $userExt->user_id = $user->id;
            $userExt->first_name = 'Иван';
            $userExt->middle_name = 'Иванов';
            $userExt->last_name = 'Иванович';
            $userExt->phone = '+79998887766';
            $userExt->populateRelation('user', $user);
            $user->populateRelation('userExt', $userExt);
        }
        if (!Email::findOne(['user_id' => $user->id])) {
            $email = new Email();
            $email->id = 1;
            $email->user_id = $user->id;
            $email->value = 'test@example.com';
            $email->generateConfirmToken();
            $email->is_confirmed = Boolean::Yes->value;
            $email->populateRelation('user', $user);
            $user->populateRelation('email', $email);
        }
        return $user;
    }

    public static function findList(): array
    {
        return self::find()->select(['name', 'id'])->indexBy('id')->column();
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t(Mail::MODULE_MESSAGES, 'ID'),
            'name' => Yii::t(Mail::MODULE_MESSAGES, 'Name'),
            'htmlTemplateFilename' => Yii::t(Mail::MODULE_MESSAGES, 'Html Template'),
            'textTemplateFilename' => Yii::t(Mail::MODULE_MESSAGES, 'Text Template')
        ];
    }

    final public function getHtmlTemplateFilename(): string
    {
        return $this->name . '-html.php';
    }

    final public function getTextTemplateFilename(): string
    {
        return $this->name . '-text.php';
    }
}
