<?php

use common\components\helpers\UserUrl;
use common\models\PromocodeSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Promocode
 */

$this->title = Yii::t('app', 'Create Promocode');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Promocodes'),
    'url' => UserUrl::setFilters(PromocodeSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="promocode-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>
