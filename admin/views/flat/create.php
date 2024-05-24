<?php

use common\components\helpers\UserUrl;
use common\models\FlatSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Flat
 * @var $rooms array
 */

$this->title = Yii::t('app', 'Create Flat');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Flats'),
    'url' => UserUrl::setFilters(FlatSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flat-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'rooms' => $rooms, 'isCreate' => true]) ?>

</div>
