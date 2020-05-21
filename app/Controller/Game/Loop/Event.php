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

    public function execute()
    {
        $maps = $this->getMaps();

        if ($maps) {
            $this->setLights();

            foreach ($maps as $k => $v) {
                co(function () use ($v) {
                    $this->frame($v);
                });
            }
        }
    }

    public function getMaps()
    {
        if (!self::$maps) {
            self::$maps = $this->GameData->getMap();
        }

        return self::$maps;
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

    //同步每一帧
    public function frame($map)
    {
        //close door 关不关门感觉无所谓~

        $this->goPlayer($map);
    }

    public function goPlayer($map)
    {
        co(function () use ($map) {
            $players = $this->GameData->getMapPlayers($map['info']['id']);
            if ($players) {
                foreach ($players as $player) {
                    $this->PlayerObject->process($player);
                }
            }
        });
    }
}
