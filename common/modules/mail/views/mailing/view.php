<?php

use admin\components\widgets\detailView\Column;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\modules\mail\{enums\MailingType, Mail, models\MailingSearch};
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model common\modules\mail\models\Mailing
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Mailings'),
    'url' => UserUrl::setFilters(MailingSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailing-view">

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
            Column::widget(['attr' => 'name']),
            Column::widget(['attr' => 'mailing_type', 'items' => MailingType::class]),
            Column::widget([
                'attr' => 'mail_template_id',
                'viewAttr' => 'mailTemplate.name',
                'pathLink' => 'mail/mail-template'
            ]),
            Column::widget(['attr' => 'mail_subject']),
            Column::widget(['attr' => 'comment'])
        ]
    ]) ?>

</div>
