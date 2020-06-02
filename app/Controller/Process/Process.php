<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;
use Swoole\Process as SwooleProcess;

/**
 * 已废弃(已使用GameProcess)...数据隔离未做好,造成数据抢占,数据污染~
 */
class Process extends AbstractController
{
    /**
     * 进程名称
     * @var string
     */
    public $name = 'DataProcess';

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

    public $startEoF = '#startEoF#';
    public $endEoF   = '#endEoF#';
    public $packLen  = 1000;

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

            //解包
            $res = '';
            while (true) {
                $info = $socket->recv();
                $res .= $info;
                if ($info == $this->endEoF) {
                    $msg = str_replace([$this->startEoF, $this->endEoF], '', $res);
                    $res = '';
                    $this->handle($process, json_decode($msg, true));
                }
            }

        }, $this->redirectStdinStdout, $this->pipeType, $this->enableCoroutine);

        $this->Server->addProcess(self::$dataProcess);
    }

    public function handle($process, $msg = null)
    {
        if ($msg && count($msg) >= 2) {

            //最多支持3个参数(多了不如放数组~)
            switch (count($msg)) {
                case 2:
                    list($model, $func) = $msg;

                    $res = $this->$model->$func();
                    break;

                case 3:
                    list($model, $func, $param1) = $msg;

                    $res = $this->$model->$func($param1);
                    break;

                case 4:
                    list($model, $func, $param1, $param2) = $msg;

                    $res = $this->$model->$func($param1, $param2);
                    break;

                case 5:
                    list($model, $func, $param1, $param2, $param3) = $msg;

                    $res = $this->$model->$func($param1, $param2, $param3);
                    break;
            }

            $process->write($this->startEoF);

            if ($res) {
                if (is_array($res)) {
                    $res = json_encode($res);
                }

                $res = $this->packData($res);
                foreach ($res as $k => $v) {
                    $process->write($v);
                }
            }

            $process->write($this->endEoF);
        }
    }

    public function packData($res)
    {
        $res = $res.'';
        $len    = strlen($res);
        $strlen = ceil($len / $this->packLen);

        $data = [];
        for ($i = 1; $i <= $strlen; $i++) {
            $data[] = substr($res, ($i - 1) * $this->packLen, $this->packLen);
        }

        return $data;
    }

    //写入(可废弃)
    public function write($msg)
    {
        $this->writeData($msg);
        $this->readData();
    }

    //读取(可废弃)
    public function read($msg)
    {
        $this->writeData($msg);
        return $this->readData();
    }

    //发送(跟函数一样调用方式)
    public function send($msg)
    {
    	$this->writeData($msg);
        return $this->readData();
    }

    public function writeData($msg)
    {
        self::$dataProcess->write($this->startEoF);
        $res = $this->packData(json_encode($msg));

        foreach ($res as $k => $v) {
            self::$dataProcess->write($v);
        }

        self::$dataProcess->write($this->endEoF);
    }

    public function readData()
    {
        try {
            $res = '';
            while (true) {
                $info = self::$dataProcess->read();

                $res .= $info;

                if ($info == $this->endEoF) {
                    break;
                }
            }

            $res = str_replace([$this->startEoF, $this->endEoF], '', $res);

            return $res ? json_decode($res, true) : false;
        } catch (\Exception $e) {
            EchoLog(sprintf('进程错误: %s', $e->getMessage()), 'w');
            return false;
        }
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
    }
}
