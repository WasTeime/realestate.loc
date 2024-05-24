<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\components\widgets\gridView\ColumnDate;
use admin\components\widgets\gridView\ColumnSwitch;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use common\models\Flat;
use kartik\grid\SerialColumn;
use yii\widgets\ListView;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\FlatSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\Flat
 */

$this->title = Yii::t('app', 'Flats');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flat-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div>
        <?=
            RbacHtml::a(Yii::t('app', 'Create Flat'), ['create'], ['class' => 'btn btn-success']);
//           $this->render('_create_modal', ['model' => $model]);
        ?>
    </div>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],

//            Column::widget(),
            Column::widget(['attr' => 'title']),
            Column::widget(['attr' => 'cost']),
            Column::widget(['attr' => 'floor']),
//            Column::widget(['attr' => 'flat_img']),
            Column::widget(['attr' => 'address']),
//            Column::widget(['attr' => 'additional_name']),
//            Column::widget(['attr' => 'additional_img']),
            ColumnSwitch::widget(['attr' => 'access_api']),
            [
                'label' => 'Всего комнат',
                'value' => fn (Flat $flat) => count($flat->rooms)
            ],
            ColumnDate::widget(['attr' => 'created_at', 'searchModel' => $searchModel, 'editable' => false]),
            ColumnDate::widget(['attr' => 'updated_at', 'searchModel' => $searchModel, 'editable' => false]),

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>
</div>
