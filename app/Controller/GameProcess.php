<?php
declare (strict_types = 1);

namespace App\Controller;

use App\Controller\AbstractController;
use Swoole\Process as SwooleProcess;

class GameProcess extends AbstractController
{
    /**
     * 进程名称
     * @var string
     */
    public $name = 'GameProcess';

    /**
     * 重定向自定义进程的标准输入和输出
     * @var bool
     */
    public $redirectStdinStdout = false;

    /**
     * 管道类型
     * @var int
     */
    public $pipeType = 2;

    /**
     * 是否启用协程
     * @var bool
     */
    public $enableCoroutine = true;

    public static $dataProcess;

    public function start($name = null)
    {
        if ($name) {
            $this->name = $name;
        }

        self::$dataProcess = new SwooleProcess(function (SwooleProcess $process) {
            $process->name(config('app_name') . '.' . $this->name);

            $socket = $process->exportSocket();

            //初始化
            $this->init();

            while (true) {
                $msg = $socket->recv();
                co(function () use ($msg) {
                    $msg = json_decode($msg, true);

                    list($cmaName, $fd, $param) = $msg;
                    $this->msgReceive($cmaName, $fd, $param);
                });
            }

        }, $this->redirectStdinStdout, $this->pipeType, $this->enableCoroutine);

        $this->Server->addProcess(self::$dataProcess);
    }

    public function msgReceive($cmaName, $fd, $param)
    {
        $this->handler($cmaName, $fd, $param);
    }

    public function send($msg)
    {
        self::$dataProcess->write(json_encode($msg));
    }

    //初始化数据
    public function init()
    {
        EchoLog('初始化数据中...', null, true);

        $this->ExpListData->init();

        $GameShopCount     = $this->GameShopData->init();
        $ItemDataCount     = $this->ItemData->init();
        $MagicDataCount    = $this->MagicData->init();
        $MonsterDataCount  = $this->MonsterData->init();
        $MovementDataCount = $this->MovementData->init();
        $NpcDataCount      = $this->NpcData->init();
        $QuestDataCount    = $this->QuestData->init();
        $RespawnDataCount  = $this->RespawnData->init();
        $SafeZoneDataCount = $this->SafeZoneData->init();
        $MapDataCount      = $this->MapData->init();

        EchoLog(sprintf('数据初始化加载 商品:%s 物品:%s 初始化物品:%s 技能:%s 怪物:%s 跳转信息:%s NPC:%s 任务:%s 重生信息:%s 安全区:%s 地图:%s',
            $GameShopCount,
            $ItemDataCount[0],
            $ItemDataCount[1],
            $MagicDataCount,
            $MonsterDataCount,
            $MovementDataCount,
            $NpcDataCount,
            $QuestDataCount,
            $RespawnDataCount,
            $SafeZoneDataCount,
            $MapDataCount
        ), null, true);

        $this->startTimer();
    }

    public function startTimer()
    {
        swoole_timer_tick(1000, function () {
            $this->Event->execute();
        });
    }
}
