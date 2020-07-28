<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Respawn extends AbstractController
{

    public static $path;

    public function __construct()
    {
        if (!self::$path) {
            self::$path = config('settings_path') . '/Envir/Routes/';
        }
    }

    public function newRespawn($map, $info)
    {
        $respawn = [
            'info'     => $info,
            'monster'  => $this->MonsterData::$monsterInfos[$info['monster_id']],
            'routes'   => [],
            'count'    => 0,
            'map'      => [
                'id'     => $map['info']['id'],
                'width'  => !empty($map['width']) ? $map['width'] : '',
                'height' => !empty($map['height']) ? $map['height'] : '',
                'info'   => [
                    'id' => $map['info']['id'],
                ],
            ],
            'interval' => time() + config('respawn_time'), //生成时间
        ];

        if (!$this->loadRoutes($respawn)) {
            return false;
        }

        return $respawn;
    }

    public function readfile($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        if (!$fp = fopen($file, 'r')) {
            EchoLog(sprintf('打开文件失败 :%s', $file), 'w');
            return false;
        }

        $lines = [];
        while (!feof($fp)) {
            if ($line = rtrim(removeBOM(stream_get_line($fp, 2048, "\n")))) {
                $lines[] = $line;
            }
        }

        fclose($fp);

        return $lines;
    }

    public function loadRoutes(&$respawn)
    {
        if (empty($respawn['info']['route_path'])) {
            return true;
        }

        $lines = $this->readfile(self::$path . $respawn['info']['route_path'] . '.txt');
        if (!$lines) {
            return false;
        }

        foreach ($lines as $key => $line) {
            $route = $this->routeInfoFromText($line);
            if (!$route) {
                EchoLog(sprintf('Route文件解析失败 :%s', $respawn['info']['route_path']), 'w');
                return false;
            }

            $respawn['routes'][] = $route;
        }
    }

    public function routeInfoFromText($text)
    {
        $arr = explode(',', $text);

        if (count($arr) != 2 && count($arr) != 3) {
            return false;
        }

        $x = intval($arr[0]);
        if (!$x) {
            return false;
        }

        $y = intval($arr[1]);
        if (!$y) {
            return false;
        }

        $delay = 0;

        if (count($arr) == 3) {
            $delay = $arr[2];
            if (!$delay) {
                return false;
            }
        }

        return [
            'location' => [
                'x' => $x,
                'y' => $y,
            ],
            'delay'    => $delay,
        ];
    }

    //怪物刷新
    public function process(&$respawn)
    {
        $this->spawn($respawn, true);
    }

    public function spawn(&$respawn, $type = true)
    {
        $monster = [];
        if($respawn)
        {
            for ($i = $respawn['count']; $i < $respawn['info']['count']; $i++) {
                if ($info = $this->spawnOne($respawn, $type)) {
                    $monster[] = $info;
                }
            }

            $respawn['count'] = $respawn['info']['count'];
        }

        return $monster;
    }

    public function spawnOne($respawn, $type)
    {
        for ($i = 0; $i < 10; $i++) {
            $x = $respawn['info']['location_x'] + rand(-$respawn['info']['spread'], $respawn['info']['spread']);
            $y = $respawn['info']['location_y'] + rand(-$respawn['info']['spread'], $respawn['info']['spread']);

            if (!$this->Map->inMap($respawn['map'], $x, $y)) {
                continue;
            }

            $point                  = ['x' => $x, 'y' => $y];
            $monster                = $this->Monster->newMonster($respawn['map'], $point, $respawn['monster']);
            $monster['direction']   = $respawn['info']['direction'];
            $monster['respawn_id']  = $respawn['info']['id'];
            $monster['object_type'] = $this->Enum::ObjectTypeMonster;

            if ($type) {
                $this->Map->addObject($monster, $monster['object_type']);

                $this->Monster->broadcastInfo($monster);

                $this->Monster->broadcastHealthChange($monster);
            }

            return $monster;
        }

        return false;
    }
}
