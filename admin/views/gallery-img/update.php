<?php

use common\components\helpers\UserUrl;
use common\models\GalleryImgSearch;
use common\models\GallerySearch;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this  yii\web\View
 * @var $model common\models\GalleryImg
 */

$this->title = Yii::t('app', 'Update Gallery Img: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Galleries'),
    'url' => UserUrl::setFilters(GallerySearch::class, ['/gallery/index'])
];
$this->params['breadcrumbs'][] = ['label' => $model->gallery->name, 'url' => ['/gallery/view', 'id' => $model->gallery->id]];
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Gallery Imgs'),
    'url' => UserUrl::setFilters(GalleryImgSearch::class)
];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="gallery-img-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => false]) ?>

</div>
