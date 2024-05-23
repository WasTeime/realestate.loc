<?php

/**
 * @var $this yii\web\View
 * @var $user common\modules\user\models\User
 */

$data = $this->params['data'] ?? [];
$resetLink = '';
if ($user) {
    $resetLink = "{$data['domain']}/site/reset-password?token=$user->password_reset_token";
}
?>
Здравствуйте, <?= $data['user_name'] ?? '' ?>!<?= PHP_EOL ?>
<?= PHP_EOL ?>
Вы получили это письмо, так как нам поступил запрос на <?= PHP_EOL ?>
восстановление Вашего пароля на сайте <?= $data['domain'] ?>.<?= PHP_EOL ?>
<?= PHP_EOL ?>
Для обновления пароля, пожалуйста, перейдите по <?= PHP_EOL ?>
ссылке: <?= $resetLink ?><?= PHP_EOL ?>
<?= PHP_EOL ?>
Если Вы не обращались к процедуре восстановления<?= PHP_EOL ?>
пароля, просто проигнорируйте данное письмо. <?= PHP_EOL ?>
Ваш пароль не будет изменен.