<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class PlayersList extends AbstractController
{
    protected $key = 'PlayersList';

    public function getPlayersList()
    {
        return json_decode($this->Redis->get($this->key), true);
    }

    public function addPlayersList($p)
    {
        $PlayersList   = $this->getPlayersList() ?: [];
        $PlayersList[] = $p;

        $this->Redis->set($this->key, json_encode($PlayersList, JSON_UNESCAPED_UNICODE));
    }

    public function delPlayersList($fd, $p = null)
    {
        if (!$p) {
            $p = $this->PlayerObject->getPlayer($fd);
        }

        if (!$p) {
            return false;
        }

        if (!empty($p['map'])) {
            $this->GameData->delMapPlayers($p['map']['info']['id'], $p);
        }

        if (!empty($p['id'])) {
            $this->delCharacter($p);
        }
    }

    public function delCharacter($p)
    {
        $this->Redis->del('player:character_id_' . $p['id']);
    }

    public function saveData($fd, $p = null)
    {
        if (!$p) {
            $p = $this->PlayerObject->getPlayer($fd);
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
}
