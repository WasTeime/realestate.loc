<?php

use admin\components\widgets\detailView\Column;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\models\GalleryImgSearch;
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model common\models\GalleryImg
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Gallery Imgs'),
    'url' => UserUrl::setFilters(GalleryImgSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gallery-img-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= RbacHtml::a(
            Yii::t('app', 'Delete'),
            ['delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post'
                ]
            ]
        ) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            Column::widget(),
            Column::widget(['attr' => 'gallery_id']),
            Column::widget(['attr' => 'img']),
            Column::widget(['attr' => 'name']),
            Column::widget(['attr' => 'text']),
            Column::widget(['attr' => 'created_at', 'format' => 'datetime']),
            Column::widget(['attr' => 'updated_at', 'format' => 'datetime']),
        ]
    ]) ?>

</div>
