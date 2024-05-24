<?php

use common\components\helpers\UserUrl;
use common\models\DocsSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Docs
 */

$this->title = Yii::t('app', 'Create Docs');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Docs'),
    'url' => UserUrl::setFilters(DocsSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="docs-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>
