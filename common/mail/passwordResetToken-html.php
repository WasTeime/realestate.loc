<?php

/**
 * @var $this yii\web\View
 * @var $user common\modules\user\models\User|null
 */

$data = $this->params['data'] ?? [];
if (empty($data['resetLink']) && $user) {
    $data['resetLink'] = "{$data['domain']}/site/reset-password?token=$user->password_reset_token";
}
$this->registerCss(file_get_contents(__DIR__ . '/' . str_replace('-html.php', '.css', basename($this->viewFile))));
echo $this->render(str_replace('-html.php', '.pug', basename($this->viewFile)), $data);
