<?php

use yii\bootstrap5\Modal;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Docs
 */
?>

<?php $modal = Modal::begin([
    'title' => Yii::t('app', 'New Docs'),
    'toggleButton' => [
        'label' => Yii::t('app', 'Create Docs'),
        'class' => 'btn btn-success'
    ]
]) ?>

<?= $this->render('_form', ['model' => $model, 'isCreate' => false]) ?>

<?php Modal::end() ?>
