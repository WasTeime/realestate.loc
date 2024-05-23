<?php

defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('YII_ENV') || define('YII_ENV', $_ENV['YII_ENV'] ?? getenv('YII_ENV') ?: 'dev');

$root_path = dirname(__DIR__);

if (file_exists($root_path . '/c3.php')) {
    require $root_path . '/c3.php';
}
require $root_path . '/vendor/autoload.php';
require_once $root_path . '/vendor/yiisoft/yii2/Yii.php';
require $root_path . '/common/config/bootstrap.php';
require $root_path . '/frontend/config/bootstrap.php';

$commonConfig = yii\helpers\ArrayHelper::merge(
    require $root_path . '/common/config/main.php',
    require $root_path . '/common/config/main-local.php'
);
$frontendConfig = yii\helpers\ArrayHelper::merge(
    require $root_path . '/frontend/config/main.php',
    require $root_path . '/frontend/config/main-local.php'
);
if (YII_ENV_TEST) {
    $commonConfig = yii\helpers\ArrayHelper::merge(
        $commonConfig,
        require $root_path . '/common/config/test.php',
        require $root_path . '/common/config/test-local.php'
    );
    $frontendConfig = yii\helpers\ArrayHelper::merge(
        $frontendConfig,
        require $root_path . '/frontend/config/test.php',
        require $root_path . '/frontend/config/test-local.php'
    );
}
$config = yii\helpers\ArrayHelper::merge($commonConfig, $frontendConfig);

date_default_timezone_set('Europe/Moscow');
error_reporting(E_ALL & ~E_NOTICE);
(new yii\web\Application($config))->run();
