<?php

use yii\bootstrap5\Modal;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Gallery
 */
?>

<?php $modal = Modal::begin([
    'title' => Yii::t('app', 'New Gallery'),
    'toggleButton' => [
        'label' => Yii::t('app', 'Create Gallery'),
        'class' => 'btn btn-success'
    ]
]) ?>

<?= $this->render('_form', ['model' => $model, 'isCreate' => false]) ?>

<?php Modal::end() ?>
