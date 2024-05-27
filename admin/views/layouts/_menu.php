<?php

use admin\components\widgets\searchMenu\SearchMenu;
use admin\models\UserAdminSearch;
use admin\modules\modelExportImport\models\ModelImportLogSearch;
use admin\modules\rbac\components\RbacNav;
use common\components\helpers\UserUrl;
use common\models\{DocsSearch, ExportListSearch, FlatSearch, Gallery, GalleryImgSearch, GallerySearch, TextSearch};
use common\modules\log\Log;
use common\modules\mail\models\{MailingLogSearch, MailingSearch, MailTemplateSearch};
use common\modules\notification\widgets\NotificationBell;
use common\modules\user\models\UserSearch;
use kartik\icons\Icon;
use yii\bootstrap5\{Html, NavBar};
use yii\helpers\ArrayHelper;
use yii\web\View;

/**
 * @var $this View
 */

NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => [
            'navbar',
            Yii::$app->themeManager->isDark ? 'navbar-dark' : 'navbar-light',
            Yii::$app->themeManager->isDark ? 'bg-dark' : 'bg-light',
            'blue-grey',
            'tint-color-5',
            'navbar-fixed-top',
            'navbar-expand-lg'
        ]
    ]
]);
echo SearchMenu::widget();
$menuItems = [];
if (!Yii::$app->user->isGuest) {
    /** @var Log $logModule */
    $logModule = Yii::$app->getModule('log');
    $menuItems = [
        [
            'label' => Icon::show('file-alt') . 'Контент',
            'items' => [
                [
                    'label' => Icon::show('align-justify') . 'Квартиры',
                    'url' => UserUrl::setFilters(FlatSearch::class, ['/flat/index'])
                ],
                [
                    'label' => Icon::show('align-justify') . 'Документы',
                    'url' => UserUrl::setFilters(DocsSearch::class, ['/docs/index'])
                ],
                [
                    'label' => Icon::show('align-justify') . 'Тексты',
                    'url' => UserUrl::setFilters(TextSearch::class, ['/text/index'])
                ],
                [
                    'label' => 'Галереи',
                    'url' => UserUrl::setFilters(GallerySearch::class, ['/gallery/index']),
                ],
            ]
        ],
        ['label' => Icon::show('folder') . 'Файловый менеджер', 'url' => ['/site/file-manager']],
    ];
    foreach (Gallery::find()->select(['id', 'name'])->indexBy('id')->all() as $gallery) {
        $menuItems[0]['items'][] = [
            'label' => Icon::show('align-justify') . $gallery->name,
            'url' => UserUrl::setFilters(GalleryImgSearch::class, ['/gallery-img/index', 'GalleryImgSearch' => ['gallery_id' => $gallery->id]])
        ];
    }
    $menuItems[] = Html::tag('div', null, ['class' => 'divider-vertical']);
    $menuItems[] = Html::tag('div', null, ['class' => 'dropdown-divider']);
    if (Yii::$app->getModule('notification')) {
        $menuItems[] = NotificationBell::widget();
    }
    $menuItems[] = Html::tag(
        'li',
        Html::a(
            sprintf('%sВыйти (%s) ', Icon::show('sign-out-alt'), Yii::$app->user->identity->username),
            ['/site/logout'],
            ['class' => 'nav-link', 'data-method' => 'POST']
        ),
        ['class' => 'nav-item skip-search']
    );
} else {
    $menuItems[] = ['label' => Icon::show('sign-in-alt') . 'Войти', 'url' => ['/site/login']];
}
echo RbacNav::widget([
    'options' => ['class' => 'nav navbar-nav ms-auto d-flex nav-pills justify-content-between'],
    'items' => $menuItems,
    'encodeLabels' => false,
    'activateParents' => true
]);
NavBar::end();
