<?php

namespace common\components;

use Yii;
use yii\redis\Connection;

class RedisConnection extends Connection
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->hostname = Yii::$app->environment->REDIS_HOSTNAME ?: 'localhost';
        $this->port = (int)(Yii::$app->environment->REDIS_PORT ?: 6379);
        $this->database = (int)(Yii::$app->environment->REDIS_DATABASE ?: 0);
        $this->password = Yii::$app->environment->REDIS_PASSWORD ?: null;
    }
}
