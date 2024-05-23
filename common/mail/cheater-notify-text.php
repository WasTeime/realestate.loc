<?php

/**
 * @var $this yii\web\View
 * @var $user common\modules\user\models\User
 */
$data = $this->params['data'] ?? [];
?>
Здравствуйте,<?= PHP_EOL ?>
На сайте <?= $data['domain'] ?>. Обнаружен потенциальный читер.<?= PHP_EOL ?>
Рекомендуем срочно проверить панель администратора.