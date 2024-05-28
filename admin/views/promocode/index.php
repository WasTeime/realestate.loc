<?php

use admin\components\GroupedActionColumn;
use admin\components\uploadForm\UploadFormWidget;
use admin\components\widgets\gridView\Column;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\SerialColumn;
use yii\helpers\Url;
use yii\widgets\ListView;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\PromocodeSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\Promocode
 */

$this->title = Yii::t('app', 'Promocodes');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="promocode-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div>
        <?= $this->render('_create_modal', ['model' => $model]) ?>
        <?= UploadFormWidget::widget([
            'action' => Url::to(['upload']),
            'btnMessage' => 'Загрузить из файла',
            'title' => 'Загрузить'
        ]) ?>
    </div>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],

//            Column::widget(),
            Column::widget(['attr' => 'user_id']),
            Column::widget(['attr' => 'promo']),

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>
</div>
