<?php

namespace common\models;

use common\components\exceptions\ModelSaveException;
use common\enums\Boolean;
use common\modules\user\{helpers\UserHelper, models\Email, models\User};
use common\widgets\reCaptcha\ReCaptchaConfig;
use common\widgets\reCaptcha\ReCaptchaValidator3;
use Yii;
use yii\base\{Exception, InvalidConfigException};

/**
 * Форма регистрации
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class SignupForm extends AppModel
{
    /**
     * Имя пользователя
     */
    public ?string $username = null;

    /**
     * Email адрес
     */
    public ?string $email = null;

    /**
     * Пароль
     */
    public ?string $password = null;

    /**
     * Согласие с правилами
     */
    public bool $rules_accepted = false;

    /**
     * Google ReCaptcha V3
     */
    public ?string $reCaptcha = null;

    /**
     * Параметры приложения
     */
    private ?array $_params;

    /**
     * {@inheritdoc}
     */
    final public function init(): void
    {
        $this->_params = Yii::$app->params;
        if (YII_ENV_TEST) {
            $this->_params['signup']['enabled_clients']['email-password'] = true;
            $this->_params['signup']['require']['rules_accepted'] = false;
            $this->_params['signup']['unique']['email'] = true;
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    final public function rules(): array
    {
        $rules = [
            [['username', 'email'], 'trim'],
            [['username', 'email', 'password', 'rules_accepted'], 'required'],
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t('app', 'Such Username is already registered')
            ],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email',
                'unique',
                'when' => fn () => $this->_params['signup']['unique']['email'] ?? false,
                'targetClass' => Email::class,
                'targetAttribute' => 'value',
                'message' => Yii::t('app', 'Such Email is already registered')
            ],
            ['password', 'string', 'min' => 6],
            ['rules_accepted', 'boolean'],
            [
                'rules_accepted',
                'required',
                'requiredValue' => Boolean::Yes->value,
                'message' => Yii::t('app', 'Must agree to the rules'),
                'when' => fn () => $this->_params['signup']['require']['rules_accepted'] ?? false
            ]
        ];
        /** @var ReCaptchaConfig $reCaptchaConfig */
        $reCaptchaConfig = Yii::$app->get('reCaptcha', false);
        // Если настроена ReCaptcha, то добавляем защиту от спама
        if (!YII_ENV_TEST && !empty($reCaptchaConfig->secretV3)) {
            $rules[] = ['reCaptcha', 'required'];
            $rules[] = ['reCaptcha', ReCaptchaValidator3::class, 'action' => false];
        }
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'rules_accepted' => Yii::t('app', 'Rules Accepted'),
        ];
    }

    /**
     * Signs user up.
     */
    final public function signup(): ?User
    {
        if (!$this->_params['signup']['enabled_clients']['email-password'] || !$this->validate()) {
            return null;
        }
        if ($transaction = Yii::$app->db->beginTransaction()) {
            try {
                $user = UserHelper::createNewUserByEmail($this->username, $this->password);
                UserHelper::createUserExt($user, Boolean::from((int)$this->rules_accepted));
                UserHelper::createUserEmail($user, $this->email);
                $transaction->commit();
                return $user;
            } catch (\Exception) {
                $transaction->rollBack();
            }
        }
        return null;
    }
}
