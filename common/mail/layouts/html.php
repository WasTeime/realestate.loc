<?php

use yii\bootstrap5\Html;

/**
 * @var $this    yii\web\View view component instance
 * @var $message yii\mail\MessageInterface the message being composed
 * @var $content string main view render result
 */
$this->registerCss(file_get_contents(__DIR__ . '/style.css'));
echo $this->render(
    'html.pug',
    ['title' => Html::encode($this->title), 'content' => $content, 'domain' => $this->params['data']['domain']]
);
