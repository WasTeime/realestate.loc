<?php

use common\components\helpers\UserUrl;
use common\models\GalleryImgSearch;
use common\models\GallerySearch;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this  yii\web\View
 * @var $model common\models\GalleryImg
 * @var $gallery_name string
 */

$this->title = Yii::t('app', 'Create Gallery Img');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Galleries'),
    'url' => UserUrl::setFilters(GallerySearch::class, ['/gallery/index'])
];
$this->params['breadcrumbs'][] = ['label' => $gallery_name, 'url' => ['/gallery/view', 'id' => $model->gallery_id]];
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Gallery Imgs'),
    'url' => UserUrl::setFilters(GalleryImgSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gallery-img-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'gallery_name' => $gallery_name, 'isCreate' => true]) ?>

</div>
