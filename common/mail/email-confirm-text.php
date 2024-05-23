<?php

/**
 * @var $this yii\web\View
 * @var $user common\modules\user\models\User
 */

$data = $this->params['data'] ?? [];

$confirmLink = null;
if ($user) {
    $confirmLink = "{$data['domain']}api/v1/user/email-confirm?token={$user->email->confirm_token}";
}
?>
    Здравствуйте, <?= $data['user_name'] ?? '' ?><?= PHP_EOL ?>
    поздравляем с успешной регистрацией на сайте <?= $data['domain'] ?><?= PHP_EOL ?>
    Для завершения регистрации на сайте перейдите по ссылке: <?= PHP_EOL ?>
<?= $confirmLink ?>