<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\components\widgets\gridView\ColumnDate;
use admin\components\widgets\gridView\ColumnSwitch;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\SerialColumn;
use yii\widgets\ListView;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\TextSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\Text
 */

$this->title = Yii::t('app', 'Texts');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="text-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div>
        <?=
            RbacHtml::a(Yii::t('app', 'Create Text'), ['create'], ['class' => 'btn btn-success']);
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
            Column::widget(['attr' => 'key']),
            Column::widget(['attr' => 'group']),
//            Column::widget(['attr' => 'text', 'format' => 'ntext']),
            Column::widget(['attr' => 'comment']),
            ColumnSwitch::widget(['attr' => 'deletable', 'editable' => false]),
            ColumnDate::widget(['attr' => 'created_at', 'searchModel' => $searchModel, 'editable' => false]),
            ColumnDate::widget(['attr' => 'updated_at', 'searchModel' => $searchModel, 'editable' => false]),

            [
                'class' => GroupedActionColumn::class,
                'visibleButtons' => [
                    'delete' => function ($model, $key, $index) {
                            return $model->deletable === 1;
                        }
                ]
            ]
        ]
    ]) ?>
</div>
