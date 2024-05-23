<?php

use admin\widgets\ckfinder\CKFinder;
use kartik\tabs\TabsX;
use yii\bootstrap5\Html;

/**
 * @var $this         yii\web\View
 * @var $sessionCache string
 */

$this->title = Yii::t('app', 'File Manager');
$this->params['breadcrumbs'][] = $this->title;
$items = [
    [
        'label' => Yii::t('app', 'Images'),
        'content' => CKFinder::widget(['resourceType' => 'Images'])
    ],
    [
        'label' => Yii::t('app', 'Files'),
        'content' => CKFinder::widget(['resourceType' => 'Files'])
    ],
    [
        'label' => Yii::t('app', 'Audio'),
        'content' => CKFinder::widget(['resourceType' => 'Audio'])
    ],
    [
        'label' => Yii::t('app', 'Video'),
        'content' => CKFinder::widget(['resourceType' => 'Video'])
    ]
] ?>
<div>
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Ключ:
    <pre><?= Yii::$app->session->get($sessionCache) ?: Yii::$app->cache->get($sessionCache) ?></pre>
    </p>
    <?= Html::a('Регенерировать ключ', ['regenerate-key'], ['class' => 'btn btn-warning']) ?>
    <?= TabsX::widget(['items' => $items, 'enableStickyTabs' => true, 'bordered' => true]) ?>
</div>

