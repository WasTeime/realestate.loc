<?php

use common\components\helpers\UserUrl;
use common\modules\mail\{Mail, models\MailTemplateSearch, models\Template};
use yii\bootstrap5\Html;

/**
 * @var $this     yii\web\View
 * @var $model    common\modules\mail\models\MailTemplate
 * @var $template Template
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Create Mail Template');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Mail Templates'),
    'url' => UserUrl::setFilters(MailTemplateSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;

//АВТОЗАПОЛНЕНИЕ НОВОГО ШАБЛОНА
$template->pugHtml = <<<PUG
p
    | Здравствуйте,&nbsp;
    =username
p
    | Поздравляем Вас с успешной регистрацией на сайте&nbsp;
    =domain
    |.
PUG;

$template->text = <<<'PHP'
<?php

/**
 * @var $this yii\web\View 
 * @var \common\modules\user\models\User|null $user 
 */
$data = $this->params['data'] ?? [];
?>

<!-- ТЕЛО ПИСЬМА -->
PHP;
?>

<div class="mail-template-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'template' => $template]) ?>

</div>
