<?php

use common\components\helpers\UserUrl;
use common\models\GallerySearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Gallery
 */

$this->title = Yii::t('app', 'Update Gallery: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Galleries'),
    'url' => UserUrl::setFilters(GallerySearch::class)
];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="gallery-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => false]) ?>

</div>
