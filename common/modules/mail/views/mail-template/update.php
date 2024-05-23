<?php

use common\components\helpers\UserUrl;
use common\modules\mail\{Mail, models\MailTemplateSearch};
use yii\bootstrap5\Html;

/**
 * @var $this     yii\web\View
 * @var $model    common\modules\mail\models\MailTemplate
 * @var $template common\modules\mail\models\Template
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Update Mail Template: {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Mail Templates'),
    'url' => UserUrl::setFilters(MailTemplateSearch::class)
];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="mail-template-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'template' => $template]) ?>

</div>
