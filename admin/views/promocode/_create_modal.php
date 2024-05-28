<?php

use yii\bootstrap5\Modal;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Promocode
 */
?>

<?php $modal = Modal::begin([
    'title' => Yii::t('app', 'New Promocode'),
    'toggleButton' => [
        'label' => Yii::t('app', 'Create Promocode'),
        'class' => 'btn btn-success'
    ]
]) ?>

<?= $this->render('_form', ['model' => $model, 'isCreate' => false]) ?>

<?php Modal::end() ?>
