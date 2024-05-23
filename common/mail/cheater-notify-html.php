<?php

/**
 * @var $this yii\web\View
 * @var $user common\modules\user\models\User
 */
$data = $this->params['data'] ?? [];

$this->registerCss(file_get_contents(__DIR__ . '/' . str_replace('-html.php', '.css', basename($this->viewFile))));
echo $this->render(str_replace('-html.php', '.pug', basename($this->viewFile)), $data);
