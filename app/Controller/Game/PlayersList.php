<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class PlayersList extends AbstractController
{
    public function delMapPlayers($fd, $p = null)
    {
        if (!$p) {
            // $p = $this->PlayerObject->getPlayer($fd);
            $p = &$this->PlayerData::$players[$fd];
        }

        if (!$p) {
            return false;
        }

        if (!empty($p['map'])) {
            $this->GameData->delMapPlayers($p['map']['info']['id'], $p);
        }
    }

    public function saveData($fd, $p = null)
    {
        if (!$p) {
            // $p = $this->PlayerObject->getPlayer($fd);
            $p = &$this->PlayerData::$players[$fd];
        }

        if (!$p) {
            return false;
        }

        co(function () use ($p) {

            if (!empty($p['map'])) {
                $data = [
                    'current_map_id'     => $p['map']['info']['id'],
                    'direction'          => $p['current_direction'],
                    'current_location_x' => $p['current_location']['x'],
                    'current_location_y' => $p['current_location']['y'],
                    'experience'         => $p['experience'],
                    'hp'                 => $p['hp'],
                    'mp'                 => $p['mp'],
                    'level'              => $p['level'],
                    // 'gold'               => $p['gold'], //改数据库会覆盖
                    'attack_mode'        => $p['a_mode'],
                    'pet_mode'           => $p['p_mode'],
                    'allow_group'        => $p['allow_group'],

                ];

                $where = [
                    'whereInfo' => [
                        'where' => [
                            ['id', '=', $p['id']],
                        ],
                    ],
                ];

                $this->CommonService->upField('character', $where, $data);
            }
        });
    }

    public function saveGold($id, $gold)
    {
        if (!$id) {
            return false;
        }

        co(function () use ($id, $gold) {
            $data = [
                'gold' => $gold, //改数据库会覆盖
            ];

            $where = [
                'whereInfo' => [
                    'where' => [
                        ['id', '=', $id],
                    ],
                ],
            ];

            $this->CommonService->upField('character', $where, $data);
        });
    }

    public function addSkill($magic)
    {
        if (!$id) {
            return false;
        }

        co(function () use ($magic) {
            $this->CommonService->save('user_magic', $magic);
        });
    }

    public function saveItemDura($item)
    {
        if (!$item['id']) {
            return false;
        }

        co(function () use ($item) {
            $data = [
                'current_dura' => $item['current_dura'],
                'max_dura'     => $item['max_dura'],
            ];

            $where = [
                'whereInfo' => [
                    'where' => [
                        ['id', '=', $item['id']],
                    ],
                ],
            ];

            $this->CommonService->upField('user_item', $where, $data);
        });
    }
}
