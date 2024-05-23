<?php

use yii\bootstrap5\Html;
use yii\helpers\{Json, Url};

/**
 * @var $this yii\web\View
 * @var $data array
 */

$this->title = Yii::t('app', 'Backup DB');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-lg-12">
        <div class="site-about">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <?= Html::tag(
            'backup-page',
            null,
            [
                ':backups-list' => Json::htmlEncode($data),
                ':tooltips-show' => 'tooltipsShow',
                'url' => [
                    'active-backups' => Url::to(['default/active-backups']),
                    'tables' => Url::to(['default/tables']),
                    'export' => Url::to(['default/export']),
                    'import' => Url::to(['default/import']),
                    'remove' => Url::to(['default/remove']),
                    'download' => Url::to(['default/download']),
                    'upload' => Url::to(['default/upload'])
                ]
            ]
        ) ?>
    </div>
</div>