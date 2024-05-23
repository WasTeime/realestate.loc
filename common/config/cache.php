<?php

use common\components\Environment;
use yii\caching\{ApcCache, FileCache, MemCache};

if (
    !is_null(Environment::readEnv('MEMCAHCED_HOSTS'))
    && !is_null(Environment::readEnv('MEMCAHCED_PORT'))
) {
    $hosts = explode(',', Environment::readEnv('MEMCAHCED_HOSTS'));
    $weight = 100 - (40 * (count($hosts) - 1));
    $servers = [];
    foreach ($hosts as $host) {
        if ($host = trim($host)) {
            $servers[] = [
                'host' => $host,
                'port' => Environment::readEnv('MEMCAHCED_PORT'),
                'weight' => $weight
            ];
            $weight = (int)($weight * 0.6);
        }
    }
    if (!empty($servers)) {
        $cache = [
            'class' => MemCache::class,
            'useMemcached' => true,
            'servers' => $servers
        ];
    }
}
if (!isset($cache)) {
    if (extension_loaded('apcu')) {
        $cache = ['class' => ApcCache::class, 'useApcu' => true];
    } else {
        $cache = ['class' => FileCache::class];
    }
}
return $cache;
