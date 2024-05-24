<?php

use admin\widgets\ckfinder\CKFinderInputFile;
use admin\widgets\dynamicForm\DynamicFormHelper;
use admin\widgets\dynamicForm\DynamicFormWidget;
use admin\widgets\input\YesNoSwitch;
use common\components\AppActiveForm;
use common\widgets\RowGroup;
use kartik\icons\Icon;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this     yii\web\View
 * @var $model    common\models\Flat
 * @var $form     AppActiveForm
 * @var $isCreate bool
 * @var $rooms array
 */
?>

<div class="flat-form">

    <?php $form = AppActiveForm::begin(['id' => 'flat-form']) ?>
    <?= RowGroup::widget([
        'form' => $form,
        'model' => $model,
        'fields' => [
            'title',
            'subtitle',
        ]
    ]) ?>

    <?= $form->field($model, 'additional_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

    <?= RowGroup::widget([
        'form' => $form,
        'model' => $model,
        'fields' => [
            'cost',
            'floor',
        ]
    ]) ?>

    <?= RowGroup::widget([
        'form' => $form,
        'model' => $model,
        'fields' => [
            'flat_img' => CKFinderInputFile::class,
            'additional_img' => CKFinderInputFile::class,
        ]
    ]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'access_api')->widget(YesNoSwitch::class) ?>

    <?php
    DynamicFormWidget::begin([
        'widgetContainer' => 'content_flat_form_wrapper',
        'widgetBody' => '.container-content_flat',
        'widgetItem' => '.content_flat',
        'min' => 1,
        'insertButton' => '.add-content_flat',
        'deleteButton' => '.remove-content_flat',
        'model' => $rooms[0],
        'formId' => 'flat-form',
        'formFields' => ['name', 'square', 'uid']
    ])
    ?>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th><?= Yii::t('app', 'Rooms') ?></th>
            <th class="text-center" style="width: 50px">
                <?= DynamicFormHelper::plusButton('add-content_flat') ?>
            </th>
        </tr>
        </thead>
        <tbody class="container-content_flat">
        <?php foreach ($rooms as $index => $room): ?>
            <tr class="content_flat">
                <td class="v-center">
                    <?= DynamicFormHelper::primaryKeyHiddenInput($room, "[$index]id") ?>

                    <?= RowGroup::widget([
                        'form' => $form,
                        'model' => $room,
                        'margin' => 'mb-1',
                        'fields' => [
                            "[$index]name",
                            "[$index]square",
                        ]
                    ]) ?>

                    <?= $form->field($room, "[$index]uid")->widget(CKFinderInputFile::class) ?>
                </td>
                </td>
                <td class="text-center v-center">
                    <?= DynamicFormHelper::minusButton('remove-content_flat') ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot>
        <tr>
            <td></td>
            <td class="text-center" style="width: 50px">
                <?= DynamicFormHelper::plusButton('add-content_flat') ?>
            </td>
        </tr>
        </tfoot>
    </table>
    <?php DynamicFormWidget::end() ?>

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
