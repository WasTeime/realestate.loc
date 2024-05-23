<?php

use common\components\{Environment, queue\AppQueue};
use yii\queue\{LogBehavior, redis\Queue};

if (
    !is_null(Environment::readEnv('REDIS_HOSTNAME'))
    && !is_null(Environment::readEnv('REDIS_PORT'))
    && !is_null(Environment::readEnv('REDIS_DATABASE'))
) {
    $queue = [
        'class' => Queue::class,
        'as log' => LogBehavior::class
    ];
} else {
    $queue = AppQueue::class;
}
return $queue;
