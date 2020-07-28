<?php

declare (strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

return [
    'app_name'                   => env('APP_NAME', 'pmir2'),
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            // LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            // LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
    // 是否使用注解扫描缓存
    'scan_cacheable'             => env('SCAN_CACHEABLE', false),

    'settings_path'              => BASE_PATH . '/storage',
    'dataRange'                  => 20, //视野范围,正常屏幕大小
    'respawn_time'               => 3*60, //怪物刷新间隔时间
    'heal_duration'              => 10, //玩家回血时长(最低为1秒)
    'dead_time'                  => 5, //怪物死亡尸体消失时间
];
