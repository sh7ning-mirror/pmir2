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

namespace HyperfTest\Cases;

use App\Controller\Game\Ai\Behavior;
use App\Controller\ObjectService;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends HttpTestCase
{
    public function __get($name)
    {
        return ObjectService::getObject($name);
    }

    public function testExample()
    {
        $monster = $this->GameData->getMapMonster(2);

        var_dump($monster);
        //测试怪物AI
        $target = [
            'id'               => 2,
            'map'              => [
                'id'     => 1,
                'width'  => 700,
                'height' => 700,
                'info'   => [
                    'id' => 1,
                ],
            ],
            'current_location' => [
                'x' => 20,
                'y' => 30,
            ],
            'dead'             => false,
        ];

        $monster = [
            'id'               => 1,
            'map'              => [
                'id'     => 1,
                'width'  => 700,
                'height' => 700,
                'info'   => [
                    'id' => 1,
                ],
            ],
            'current_location' => [
                'x' => 20,
                'y' => 30,
            ],
            // 'target'           => [
            //     'id'    => $target['id'],
            //     'mapId' => $target['map']['id'],
            //     'object_type' => 1

            // ],
            'move_time'        => time(),
            'view_range'       => 10,
            'object_type'      => 5,
            'ai' => 2
        ];

        //设置怪物
        $this->GameData->setMapMonster(1, $monster);

        //设置玩家 10个
        for ($i = 1; $i <= 10; $i++) {
            $target['id'] = $i;
            $this->GameData->setMapPlayers(1, $target);
            $this->GameData->setPlayer($target['id'], $target);
        }

        $obj = $this->Behavior->behavior(2, [
            'id'    => 1,
            'mapId' => 1,
        ]);

        if ($obj['root']) {
            $obj['root']->Start();
        }
    }
}
