<?php

use admin\widgets\ckfinder\CKFinderInputFile;
use admin\widgets\dynamicForm\DynamicFormHelper;
use admin\widgets\dynamicForm\DynamicFormWidget;
use common\components\AppActiveForm;
use common\widgets\RowGroup;
use kartik\icons\Icon;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this     yii\web\View
 * @var $model    common\models\Gallery
 * @var $form     AppActiveForm
 * @var $isCreate bool
 */
?>

<div class="gallery-form">

    <?php $form = AppActiveForm::begin(['id' => 'gallery-form']) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
    DynamicFormWidget::begin([
        'widgetContainer' => 'content_gallery_form_wrapper',
        'widgetBody' => '.container-content_gallery',
        'widgetItem' => '.content_gallery',
        'min' => 1,
        'insertButton' => '.add-content_gallery',
        'deleteButton' => '.remove-content_gallery',
        'model' => $galleryImgs[0],
        'formId' => 'gallery-form',
        'formFields' => ['name', 'text', 'img']
    ])
    ?>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th><?= Yii::t('app', 'Gallery Images') ?></th>
            <th class="text-center" style="width: 50px">
                <?= DynamicFormHelper::plusButton('add-content_gallery') ?>
            </th>
        </tr>
        </thead>
        <tbody class="container-content_gallery">
        <?php foreach ($galleryImgs as $index => $img): ?>
            <tr class="content_gallery">
                <td class="v-center">
                    <?= DynamicFormHelper::primaryKeyHiddenInput($img, "[$index]id") ?>

                    <?= $form->field($img, "[$index]img")->widget(CKFinderInputFile::class) ?>

                    <?= RowGroup::widget([
                        'form' => $form,
                        'model' => $img,
                        'margin' => 'mb-1',
                        'fields' => [
                            "[$index]name",
                            "[$index]text",
                        ]
                    ]) ?>
                </td>
                </td>
                <td class="text-center v-center">
                    <?= DynamicFormHelper::minusButton('remove-content_gallery') ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot>
        <tr>
            <td></td>
            <td class="text-center" style="width: 50px">
                <?= DynamicFormHelper::plusButton('add-content_gallery') ?>
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
