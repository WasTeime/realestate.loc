<?php

use common\components\helpers\UserUrl;
use common\models\FlatSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Flat
 * @var  $rooms common\models\Room[]
 */

$this->title = Yii::t('app', 'Update Flat: {name}', [
    'name' => $model->title,
]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Flats'),
    'url' => UserUrl::setFilters(FlatSearch::class)
];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="flat-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'rooms' => $rooms, 'isCreate' => false]) ?>

</div>
