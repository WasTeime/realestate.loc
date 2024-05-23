<?php

use admin\components\widgets\detailView\Column;
use admin\modules\rbac\components\RbacHtml;
use common\components\{helpers\UserUrl};
use common\modules\mail\{Mail, models\MailTemplate, models\MailTemplateSearch, models\Template};
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\DetailView;

/**
 * @var $this  View
 * @var $model common\modules\mail\models\MailTemplate
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Mail Templates'),
    'url' => UserUrl::setFilters(MailTemplateSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
$htmlFile = "@root/common/mail/$model->name-html.php";
$textFile = "@root/common/mail/$model->name-text.php";
$template = Template::findFiles($model->name);
$pugLayout = Json::htmlEncode($template->pugLayout);
$layoutStyle = Json::htmlEncode($template->layoutStyle);
$pugHtml = Json::htmlEncode($template->pugHtml);
$style = Json::htmlEncode($template->style);
$url = '/admin/mail/mail-template/render-pug';
if ($renderAvailable = RbacHtml::isAvailable($url)) {
    $this->registerJs(
        <<<JS
function writeIframeText(text) {
  const iframe = document.getElementById('html-preview')
  const doc = iframe.contentWindow?.document || iframe.contentDocument
  doc.open()
  doc.write(text)
  doc.close()
  setTimeout(() => {
    const body = doc.body
    iframe.style.width = '100%'
    iframe.style.height = (body.offsetHeight + body.scrollHeight - body.clientHeight) + 'px'
    iframe.style.maxHeight = '80vh'
    iframe.style.minWidth = '600px'
  }, 50)
} 
$.post('$url', { layout: '$pugLayout', layoutStyle: '$layoutStyle', content: '$pugHtml', style: '$style' })
  .then(response => writeIframeText(response))
  .catch(reason => writeIframeText('<pre>' + reason.responseText + '</pre>'))
JS
    );
}
?>
<div class="mail-template-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(
            Yii::t('app', 'Update'),
            ['update', 'id' => $model->id],
            ['class' => 'btn btn-primary']
        ) ?>
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
            Column::widget(['attr' => 'name'])
        ]
    ]) ?>
    <?php if ($renderAvailable): ?>
        <strong>
            <?= Yii::t(Mail::MODULE_MESSAGES, 'Html Template') ?>
        </strong>
        <div>
            <iframe id="html-preview"></iframe>
        </div>
    <?php endif ?>
    <b><?= Yii::t(Mail::MODULE_MESSAGES, 'Text Template') ?></b>
    <div class="mail-preview">
        <?php
        if (file_exists(Yii::getAlias($textFile))) {
            $this->params['data']['domain'] = Yii::$app->request->hostInfo;
            echo nl2br(
                Yii::$app->view->renderFile(
                    $textFile,
                    ['user' => MailTemplate::getDummyUser()]
                )
            );
        } else {
            echo 'NOT FOUND';
        } ?>
    </div>
</div>
