<?php

namespace common\modules\mail\models;

use common\models\AppActiveRecord;
use common\modules\mail\{enums\MailingType, Mail};
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%mailing}}"
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int               $id               [int] ID
 * @property string            $name             [varchar(255)] Название
 * @property int               $mailing_type     [int] Типа рассылки
 * @property int               $mail_template_id [int] ID шаблона
 * @property string            $mail_subject     [varchar(255)] Тема рассылки
 * @property string            $comment          [varchar(255)] Комментарий
 *
 * @property-read MailTemplate $mailTemplate
 */
class Mailing extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%mailing}}';
    }

    public static function findList(): array
    {
        return self::find()->select(['name', 'id'])->indexBy('id')->column();
    }

    /**
     * {@inheritdoc}
     */
    public static function externalAttributes(): array
    {
        return ['mailTemplate.name'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'mailing_type', 'mail_template_id', 'mail_subject'], 'required'],
            [['mailing_type', 'mail_template_id'], 'integer'],
            MailingType::validator('mailing_type'),
            [['name', 'mail_subject', 'comment'], 'string', 'max' => 255],
            [
                'mail_template_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => MailTemplate::class,
                'targetAttribute' => ['mail_template_id' => 'id']
            ],
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
            'mailing_type' => Yii::t(Mail::MODULE_MESSAGES, 'Mailing Type'),
            'mail_template_id' => Yii::t(Mail::MODULE_MESSAGES, 'Mail Template'),
            'mail_subject' => Yii::t(Mail::MODULE_MESSAGES, 'Mail Subject'),
            'comment' => Yii::t(Mail::MODULE_MESSAGES, 'Comment'),
        ];
    }

    final public function getMailTemplate(): ActiveQuery
    {
        return $this->hasOne(MailTemplate::class, ['id' => 'mail_template_id']);
    }
}
