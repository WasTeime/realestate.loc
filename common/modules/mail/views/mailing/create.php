<?php

use common\components\helpers\UserUrl;
use common\modules\mail\{Mail, models\MailingSearch};
use yii\bootstrap5\Html;

/**
 * @var $this yii\web\View
 * @var $model common\modules\mail\models\Mailing
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Create Mailing');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Mailings'),
    'url' => UserUrl::setFilters(MailingSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailing-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model]) ?>

</div>
