<?php

use common\components\AppActiveForm;
use common\widgets\reCaptcha\ReCaptcha3;
use yii\bootstrap5\Html;

/**
 * @var $this yii\web\View
 * @var $form  common\components\AppActiveForm
 * @var $model admin\models\LoginForm
 */

$this->title = 'Войти';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Пожалуйста заполните указанные ниже поля:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = AppActiveForm::begin(['id' => 'login-form']) ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>

            <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'password']) ?>

            <?= $form->field($model, 'rememberMe')->checkbox() ?>

            <?= !YII_ENV_TEST && !empty(Yii::$app->reCaptcha->siteKeyV3)
                ? $form->field($model, 'reCaptcha')->label(false)->widget(ReCaptcha3::class)
                : null ?>

            <div class="form-group">
                <?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

            <?php AppActiveForm::end() ?>
        </div>
    </div>
</div>
