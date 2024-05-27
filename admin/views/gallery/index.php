<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\components\widgets\gridView\ColumnDate;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use common\components\helpers\UserUrl;
use common\models\GalleryImgSearch;
use kartik\grid\SerialColumn;
use kartik\icons\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\GallerySearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\Gallery
 */

$this->title = Yii::t('app', 'Galleries');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gallery-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div>
        <?=
            RbacHtml::a(Yii::t('app', 'Create Gallery'), ['create'], ['class' => 'btn btn-success']);
        ?>
    </div>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],

//            Column::widget(),
            Column::widget(['attr' => 'name']),
            Column::widget(['attr' => 'countImages', 'editable' => false]),
            ColumnDate::widget(['attr' => 'created_at', 'searchModel' => $searchModel, 'editable' => false]),
            ColumnDate::widget(['attr' => 'updated_at', 'searchModel' => $searchModel, 'editable' => false]),
            [
                'class' => GroupedActionColumn::class,
                'template' => '{view} {images} {update} {delete}',
                'buttons' => [
                    'images' => function ($url, $model, $key) {
                        return Html::a(
                            Icon::show('list'),
                            UserUrl::setFilters(
                                GalleryImgSearch::class,
                                ['/gallery-img/index', 'GalleryImgSearch' => ['gallery_id' => $model->id]]),
                            ['data-pjax' => '0']
                        );
                    }
                ],
            ]
        ]
    ]) ?>
</div>
