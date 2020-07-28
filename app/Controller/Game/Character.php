<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Character extends AbstractController
{
    public function characterBase($name, $class, $gender)
    {
        $startPoint = $this->GameData->randomStartPoint();
        $Enum       = $this->Enum;

        $characterInfo = [
            'name'               => $name,
            'level'              => 1,
            'class'              => $class,
            'gender'             => $gender,
            'hair'               => 1,

            'current_map_id'     => $startPoint['map_id'],
            'current_location_x' => $startPoint['location_x'],
            'current_location_y' => $startPoint['location_y'],
            'bind_map_id'        => $startPoint['map_id'],
            'bind_location_x'    => $startPoint['location_x'],
            'bind_location_y'    => $startPoint['location_y'],

            'direction'          => $Enum::MirDirectionDown,
            'hp'                 => 15,
            'mp'                 => 17,
            'experience'         => 0,
            'attack_mode'        => $Enum::AttackModeAll,
            'pet_mode'           => $Enum::PetModeBoth,
        ];

        return $characterInfo;
    }

    public function getAccountCharacters($account)
    {
        $where = [
            'whereInfo' => [
                'where' => [
                    ['b.account', '=', $account],
                    ['c.isdel', '=', 1],
                ],
            ],
            'field'     => [
                'a.character_id',
                'b.id',
                'b.login_date',
                'c.name',
                'c.level',
                'c.class',
                'c.gender',
            ],
            'join'      => [
                ['left', 'account as b', 'b.id', '=', 'a.account_id'],
                ['inner', 'character as c', 'c.id', '=', 'a.character_id'],
            ],
            'pageInfo'  => false,
        ];

        $res  = $this->CommonService->getList('account_character as a', $where);
        $data = [];
        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                $info = [
                    'index'       => $v['character_id'],
                    'name'        => $v['name'],
                    'level'       => $v['level'],
                    'class'       => $v['class'],
                    'gender'      => $v['gender'],
                    'last_access' => $v['login_date'],
                ];

                $data[] = $info;
            }
        }

        return $data;
    }
}
