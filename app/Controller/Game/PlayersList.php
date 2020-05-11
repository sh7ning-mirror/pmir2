<?php
namespace App\Controller\Game;

/**
 *
 */
class PlayersList
{
    protected $key = 'PlayersList';

    public function getPlayersList()
    {
        return json_decode(getObject('Redis')->get($this->key), true);
    }

    public function addPlayersList($p)
    {
        $PlayersList   = $this->getPlayersList() ?: [];
        $PlayersList[] = $p;

        getObject('Redis')->set($this->key, json_encode($PlayersList, JSON_UNESCAPED_UNICODE));
    }

    public function delPlayersList($fd, $p = null)
    {
        if (!$p) {
            $p = getObject('PlayerObject')->getPlayer($fd);
        }

        if (!$p) {
            return false;
        }

        if (!empty($p['Map'])) {
            getObject('GameData')->delMapPlayers($p['Map']['Info']['id'], $p);
        }

        if (!empty($p['ID'])) {
            $this->delCharacter($p);
        }
    }

    public function delCharacter($p)
    {
        getObject('Redis')->del('player:character_id_' . $p['ID']);
    }

    public function saveData($fd, $p = null)
    {
        if (!$p) {
            $p = getObject('PlayerObject')->getPlayer($fd);
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
                    'gold'               => $p['Gold'],
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

                getObject('CommonService')->upField('character', $where, $data);
            }
        });
    }
}
