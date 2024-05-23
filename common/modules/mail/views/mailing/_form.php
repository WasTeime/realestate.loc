<?php

use admin\widgets\input\Select2;
use common\components\AppActiveForm;
use common\modules\mail\{enums\MailingType, models\MailTemplate};
use kartik\icons\Icon;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\modules\mail\models\Mailing
 * @var $form  AppActiveForm
 */
?>

<div class="mailing-form">

    <?php $form = AppActiveForm::begin() ?>
    <div class="row">
        <div class="col-4">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'mailing_type')->widget(
                Select2::class,
                [
                    'data' => MailingType::indexedDescriptions(),
                    'hideSearch' => true
                ]
            ) ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'mail_template_id')
                ->widget(Select2::class, ['data' => MailTemplate::findList()]) ?>
        </div>
    </div>
    <?= $form->field($model, 'mail_subject')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save'),
            ['class' => 'btn btn-success']
        ) ?>
    </div>

    <?php AppActiveForm::end() ?>

</div>
