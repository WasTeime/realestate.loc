<?php

use admin\components\consoleRunner\ConsoleRunner;
use admin\components\ThemeManager;
use admin\models\UserAdmin;
use common\components\{DbConnection,
    Environment,
    ErrorHandler,
    queue\AppQueue,
    RedisConnection,
    Request,
    UserFormatter,
    UserScreener,
    UserUrlManager,
    UserView};
use common\modules\mail\components\Email;
use common\widgets\reCaptcha\ReCaptchaConfig;
use yii\caching\{ApcCache, FileCache};
use yii\base\Application;
use yii\console\Application as ConsoleApplication;
use yii\queue\redis\Queue;
use yii\rbac\DbManager;
use yii\symfonymailer\Mailer;
use yii\web\{Application as WebApplication, User};

/**
 * This class only exists here for IDE (PHPStorm/Netbeans/...) autocompletion.
 * This file is never included anywhere.
 * Adjust this file to match classes configured in your application config, to enable IDE autocompletion for custom components.
 */
class Yii
{
    public static ConsoleApplication|__Application|WebApplication $app;
}

/**
 * @property User|__WebUser     $user
 * @property ApcCache|FileCache $cache
 */
abstract class __Application extends Application
{
    public ConsoleRunner $consoleRunner;
    public Environment $environment;
    public RedisConnection $redis;
    public ThemeManager $themeManager;
    public DbManager $authManager;
    public Mailer $mailer;
    public DbConnection $db;
    public ErrorHandler $errorHandler;
    public Request $request;
    public AppQueue|Queue $queue;
    public UserFormatter $formatter;
    public UserScreener $screener;
    public UserUrlManager $urlManager;
    public Email $mail;
    public ReCaptchaConfig $reCaptcha;
    public UserView $view;
}

/**
 * @property common\modules\user\models\User|UserAdmin|null $identity
 */
class __WebUser
{
}
