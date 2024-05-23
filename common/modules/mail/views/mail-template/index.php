<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use admin\widgets\tooltip\TooltipWidget;
use common\modules\mail\Mail;
use kartik\grid\SerialColumn;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\modules\mail\models\MailTemplateSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Mail Templates');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mail-template-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(
            Yii::t(Mail::MODULE_MESSAGES, 'Create Mail Template'),
            ['create'],
            ['class' => 'btn btn-success']
        ) ?>
    </p>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            Column::widget(['attr' => 'name', 'editable' => false]),
            Column::widget(['attr' => 'htmlTemplateFilename', 'editable' => false]),
            Column::widget(['attr' => 'textTemplateFilename', 'editable' => false]),

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>

</div>
