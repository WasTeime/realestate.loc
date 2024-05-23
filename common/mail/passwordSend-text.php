<?php

/**
 * @var $this yii\web\View
 * @var $user common\modules\user\models\User
 */

$password = $this->params['data']['password'] ?? 'not_set';

?>
Ваш новый пароль:<?= PHP_EOL ?>

<?= $password ?>
