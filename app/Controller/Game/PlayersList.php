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

        if (!empty($p['Map'])) {
            $this->GameData->delMapPlayers($p['Map']['Info']['id'], $p);
        }

        if (!empty($p['ID'])) {
            $this->delCharacter($p);
        }
    }

    public function delCharacter($p)
    {
        $this->Redis->del('player:character_id_' . $p['ID']);
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

            if (!empty($p['Map'])) {
                $data = [
                    'current_map_id'     => $p['Map']['Info']['id'],
                    'direction'          => $p['CurrentDirection'],
                    'current_location_x' => $p['CurrentLocation']['X'],
                    'current_location_y' => $p['CurrentLocation']['Y'],
                    'experience'         => $p['Experience'],
                    'hp'                 => $p['HP'],
                    'mp'                 => $p['MP'],
                    'level'              => $p['Level'],
                    // 'gold'               => $p['Gold'], //改数据库会覆盖
                    'attack_mode'        => $p['AMode'],
                    'pet_mode'           => $p['PMode'],
                    'allow_group'        => $p['AllowGroup'],

                ];

                $where = [
                    'whereInfo' => [
                        'where' => [
                            ['id', '=', $p['ID']],
                        ],
                    ],
                ];

                $this->CommonService->upField('character', $where, $data);
            }
        });
    }
}
