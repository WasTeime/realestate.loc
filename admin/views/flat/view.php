<?php

use admin\components\widgets\detailView\Column;
use admin\components\widgets\detailView\ColumnImage;
use admin\components\widgets\gridView\ColumnSelect2;
use admin\components\widgets\gridView\ColumnSwitch;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\enums\Boolean;
use common\models\FlatSearch;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Flat
 */

$this->title = $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Flats'),
    'url' => UserUrl::setFilters(FlatSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flat-view">

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
            [
                'label' => Yii::t('app', 'Rooms'),
                'format' => 'html',
                'value' => function () use ($model) {
                    foreach ($model->rooms as $key => $room) {
                        $res = "<p>Название: $room->name</p>";
                        $res .= "<p>Площадь: $room->square</p>";
                        return $res . "Фото: ".Html::img($room->uid, ['width' => 150]);
                    }
                }
            ],
            Column::widget(['attr' => 'title']),
            Column::widget(['attr' => 'subtitle']),
            Column::widget(['attr' => 'description', 'format' => 'ntext']),
            Column::widget(['attr' => 'cost']),
            Column::widget(['attr' => 'floor']),
            ColumnImage::widget(['attr' => 'flat_img']),
            Column::widget(['attr' => 'address']),
            Column::widget(['attr' => 'additional_name']),
            ColumnImage::widget(['attr' => 'additional_img']),
            Column::widget(['attr' => 'access_api', 'items' => Boolean::class]),
            Column::widget(['attr' => 'created_at', 'format' => 'datetime']),
            Column::widget(['attr' => 'updated_at', 'format' => 'datetime']),
        ]
    ]) ?>

</div>
