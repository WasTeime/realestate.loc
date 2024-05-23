<?php

namespace common\modules\user\models;

use common\modules\user\helpers\UserHelper;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Trait ResettablePassword
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait ResettablePassword
{
    /**
     * Find user by password reset token
     *
     * @throws HttpException
     */
    public static function findIdentityByPasswordResetToken(string $token): ?self
    {
        if (!$user = self::findOne(['password_reset_token' => $token])) {
            return null;
        }
        return UserHelper::checkUserStatus($user);
    }

    /**
     * Finds out if password reset token is valid
     */
    public static function isPasswordResetTokenValid(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * Remove password reset token
     */
    final public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
        $this->save();
    }

    /**
     * Generate password reset token
     *
     * @throws Exception
     */
    final public function generatePasswordResetToken(): void
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Validate password
     */
    final public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Set new password
     *
     * @throws Exception
     */
    final public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
}
