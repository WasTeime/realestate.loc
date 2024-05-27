<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\components\widgets\gridView\ColumnImage;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use common\components\helpers\UserUrl;
use common\models\GallerySearch;
use kartik\grid\SerialColumn;
use yii\helpers\Url;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\GalleryImgSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\GalleryImg
 */

$this->title = Yii::t('app', 'Gallery Imgs');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Galleries'),
    'url' => UserUrl::setFilters(GallerySearch::class, ['/gallery/index'])
];
$this->params['breadcrumbs'][] = ['label' => $searchModel->gallery->name, 'url' => ['/gallery/view', 'id' => $searchModel->gallery->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gallery-img-index">
    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div>
        <?=
            RbacHtml::a(Yii::t('app', 'Create Gallery Img'), ['create', 'gallery_id' => $searchModel->gallery->id], ['class' => 'btn btn-success']);
//           $this->render('_create_modal', ['model' => $model]);
        ?>
    </div>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],

////            Column::widget(),
//            Column::widget([
//                'attr' => 'gallery_id',
//            ], ['value' => fn (\common\models\GalleryImg $galleryImg) => $galleryImg->gallery->name]),
            ColumnImage::widget(['attr' => 'img']),
            Column::widget(['attr' => 'name']),
            Column::widget(['attr' => 'text']),
//            ColumnDate::widget(['attr' => 'created_at', 'searchModel' => $searchModel, 'editable' => false]),
//            ColumnDate::widget(['attr' => 'updated_at', 'searchModel' => $searchModel, 'editable' => false]),

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>
</div>
