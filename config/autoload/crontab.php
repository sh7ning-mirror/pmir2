<?php
use Hyperf\Crontab\Crontab;

return [
    // 是否开启定时任务
    'enable' => true,

    // 通过配置文件定义的定时任务
    'crontab' => [
        // (new Crontab())->setName('Loop')->setRule('* * * * * *')->setCallback([App\Controller\Game\Loop\Event::class, 'execute'])->setMemo('事件定时器'),
    ],
];