<?php
declare (strict_types = 1);

namespace App\Controller\Game\Loop;

use App\Controller\AbstractController;

/**
 *  事件定时器
 */
class Event extends AbstractController
{
    public static $maps = []; //地图

    public static $hour; //当前时辰

    public static $nowTime;

    public function execute()
    {
        co(function(){
            if (!self::$nowTime) {
                self::$nowTime = time();
            }

            $maps = $this->getMaps();

            if ($maps) {
                $this->setLights();

                foreach ($maps as $k => $v) {
                    co(function () use ($v) {
                        $this->frame($v);
                    });
                }
            }
        });
    }

    public function getMaps()
    {
        return $this->GameData->getMapIds();
    }

    //灯光控制
    public function setLights()
    {
        co(function () {
            $nowHour = date('H');

            if ($nowHour != self::$hour) {
                self::$hour = $nowHour;

                $this->sendQuery(['TIME_OF_DAY', ['lights' => $this->Settings->lightSet()]]);
            }
        });
    }

    //全局同步信息
    public function sendQuery($msg, $fd = null)
    {
        if ($fd) {
            $this->SendMsg->send($fd, $msg);
        } else {
            foreach ($this->Server->connections as $fd) {
                co(function () use ($fd, $msg) {
                    $this->SendMsg->send($fd, $msg);
                });
            }
        }
    }

    //同步
    public function frame($mapId)
    {
        //close door 关不关门感觉无所谓~

        $this->goPlayer($mapId);

        $this->goRespawns($mapId);

        $this->goNpc($mapId);

        $this->goMonster($mapId);
    }

    public function goPlayer($mapId)
    {
        co(function () use ($mapId) {
            $players = $this->GameData->getMapPlayers($mapId);
            if ($players) {
                foreach ($players as $player) {
                    $this->PlayerObject->process($player);
                }
            }
        });
    }

    public function goRespawns($mapId)
    {
        $time = time();
        if ($time < self::$nowTime + config('respawn_time')) {
            return;
        }

        self::$nowTime = $time;

        co(function () use ($mapId, $time) {
            $respawns = $this->GameData->getMapRespawns($mapId);
            if ($respawns) {
                foreach ($respawns as $k => $respawn) {
                    if (empty($respawn['interval']) || $time < $respawn['interval']) {
                        break;
                    }
                    $this->Respawn->process($respawn);
                    $respawns[$k] = $respawn;
                }

                co(function () use ($mapId, $respawns) {
                    $this->GameData->setMapRespawns($mapId, $respawns);
                });
            }
        });
    }

    public function goNpc($mapId)
    {
        $npcs = $this->GameData->getMapNpcs($mapId);

        co(function () use ($mapId, $npcs) {
            foreach ($npcs as $k => $v) {
                $this->Npc->process($v);
                $npcs[$k] = $v;
            }

            $this->GameData->setMapNpcs($mapId, $npcs);
        });
    }

    //执行玩家周边怪物AI(全局怪物太费资源)
    public function goMonster($mapId)
    {
        co(function() use($mapId){
            $players = $this->GameData->getMapPlayers($mapId);
            $moreMonsters = [];

            if ($players) {
                foreach ($players as $k => $v) {
                    $monsters = $this->Map->getCellMonster($v,40);
                    if($monsters)
                    {
                        foreach ($monsters as  $monster) {
                            $moreMonsters[$monster['id']] = $monster;
                        }
                    }
                }
            }

            if($moreMonsters)
            {
                foreach ($moreMonsters as $monster) 
                {
                    $this->Monster->process($monster);
                }
            }
        });
    }
}
