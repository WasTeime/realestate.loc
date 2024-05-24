<?php

use admin\widgets\ckeditor\EditorClassic;
use admin\widgets\input\YesNoSwitch;
use common\components\AppActiveForm;
use common\widgets\RowGroup;
use kartik\icons\Icon;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this     yii\web\View
 * @var $model    common\models\Text
 * @var $form     AppActiveForm
 * @var $isCreate bool
 */
?>

<div class="text-form">

    <?php $form = AppActiveForm::begin() ?>

    <?=
    RowGroup::widget([
        'form' => $form,
        'model' => $model,
        'fields' => [
            'key',
            'group'
        ]
    ])
    ?>

    <?= $form->field($model, 'text')->widget(EditorClassic::class) ?>

    <?= $form->field($model, 'deletable')->widget(YesNoSwitch::class) ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?php if ($isCreate) {
            echo Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save And Create New'),
                ['class' => 'btn btn-success', 'formaction' => Url::to() . '?redirect=create']
            );
            echo Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save And Return To List'),
                ['class' => 'btn btn-success', 'formaction' => Url::to() . '?redirect=index']
            );
        } ?>
        <?= Html::submitButton(Icon::show('save') . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php AppActiveForm::end() ?>

</div>
