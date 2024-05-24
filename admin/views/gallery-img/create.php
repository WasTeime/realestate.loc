<?php

use common\components\helpers\UserUrl;
use common\models\GalleryImgSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\GalleryImg
 */

$this->title = Yii::t('app', 'Create Gallery Img');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Gallery Imgs'),
    'url' => UserUrl::setFilters(GalleryImgSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gallery-img-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>
