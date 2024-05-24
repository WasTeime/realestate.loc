<?php

use common\components\helpers\UserUrl;
use common\models\GallerySearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Gallery
 */

$this->title = Yii::t('app', 'Create Gallery');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Galleries'),
    'url' => UserUrl::setFilters(GallerySearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gallery-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>
