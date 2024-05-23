<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\{gridView\Column, gridView\ColumnSelect2};
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use admin\widgets\tooltip\TooltipWidget;
use common\modules\mail\{enums\MailingType, Mail, models\MailTemplate};
use kartik\grid\SerialColumn;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\modules\mail\models\MailingSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Mailings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailing-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(
            Yii::t(Mail::MODULE_MESSAGES, 'Create Mailing'),
            ['create'],
            ['class' => 'btn btn-success']
        ) ?>
        <?= RbacHtml::a(
            Yii::t(Mail::MODULE_MESSAGES, 'Test Mailing') . ' ' .
            TooltipWidget::widget(
                ['title' => Yii::t(Mail::MODULE_MESSAGES, 'Form for sending test emails')]
            ),
            ['test'],
            ['class' => 'btn btn-info']
        ) ?>
    </p>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            Column::widget(['attr' => 'name']),
            ColumnSelect2::widget(['attr' => 'mailing_type', 'items' => MailingType::class, 'hideSearch' => true]),
            ColumnSelect2::widget([
                'attr' => 'mail_template_id',
                'items' => MailTemplate::find()->select(['name', 'id'])->indexBy('id')->column()
            ]),
            Column::widget(['attr' => 'mail_subject']),

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>
</div>
