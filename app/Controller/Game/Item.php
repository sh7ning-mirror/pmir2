<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Item extends AbstractController
{
    public function newGold($map, $gold)
    {
        return [
            'ID'   => $this->Atomic->newObjectID(),
            'Map'  => $map,
            'Gold' => $gold,
        ];
    }

    public function newItem($map, $item)
    {
        $newItem = [
            'Gold'     => 0,
            'UserItem' => $item,
            'Name'     => $item['Info']['name'],
            'ID'       => $this->Atomic->newObjectID(),
            'Map'      => $map,
        ];

        if ($item['Info']['grade'] == $this->Enum::ItemGradeNone) {
            $newItem['NameColor'] = $this->Enum::ColorWhite;
        }

        if ($item['Info']['grade'] == $this->Enum::ItemGradeCommon) {
            $newItem['NameColor'] = $this->Enum::ColorWhite;
        }

        if ($item['Info']['grade'] == $this->Enum::ItemGradeRare) {
            $newItem['NameColor'] = $this->Enum::ColorDeepSkyBlue;
        }

        if ($item['Info']['grade'] == $this->Enum::ItemGradeLegendary) {
            $newItem['NameColor'] = $this->Enum::ColorDarkOrange;
        }

        if ($item['Info']['grade'] == $this->Enum::ItemGradeMythical) {
            $newItem['NameColor'] = $this->Enum::ColorPlum;
        }

        return $newItem;
    }

    public function drop($item, $center, $distance)
    {
        $mapInfo = $this->GameData->getMap($item['Map']['Info']['id']);
        $ok      = $this->Map->rangeCell($item, $mapInfo, $center, $distance, function ($item, $map_id, $x, $y) {

            $point = ['X' => $x, 'Y' => $y];
            $ishas = $this->GameData->getMapItem($map_id, $point);

            if ($ishas) {
                return true;
            }

            $item['CurrentLocation'] = $point;
            $this->Map->addObject($item, $this->Enum::ObjectTypeItem);

            $this->Item->broadcastInfo($item);

            return false;
        });

        return $ok;
    }

    public function broadcastInfo($item)
    {
        if (!empty($item['UserItem'])) {
            $this->broadcast($item, $this->MsgFactory->objectItem($item));
        } else {
            $this->broadcast($item, $this->MsgFactory->objectGold($item));
        }
    }

    public function broadcast($item, $msg)
    {
        $this->Map->broadcastP($item['CurrentLocation'], $msg, $item);
    }

    public function getImage($item)
    {
        $info = $item['UserItem']['Info'];

        switch ($info['type']) {
            case $this->Enum::ItemTypeAmulet:
                if ($info['stack_size'] > 0) {
                    switch ($info['shape']) {
                        case 0:
                            if ($item['UserItem']['count'] >= 300) {
                                return 3662;
                            }

                            if ($item['UserItem']['count'] >= 200) {
                                return 3661;
                            }

                            if ($item['UserItem']['count'] >= 100) {
                                return 3660;
                            }

                            return 3660;
                            break;
                        case 1:
                            if ($item['UserItem']['count'] >= 150) {
                                return 3675;
                            }

                            if ($item['UserItem']['count'] >= 100) {
                                return 2960;
                            }

                            if ($item['UserItem']['count'] >= 50) {
                                return 3674;
                            }

                            return 3673;
                            break;

                        case 2:
                            if ($item['UserItem']['count'] >= 150) {
                                return 3672;
                            }

                            if ($item['UserItem']['count'] >= 100) {
                                return 2961;
                            }

                            if ($item['UserItem']['count'] >= 50) {
                                return 3671;
                            }

                            return 3670;
                            break;
                    }
                }
                break;
        }

        return $info['image'];
    }

    public function price($item)
    {
        if (empty($item['Info'])) {
            return 0;
        }

        $p = $item['Info']['price'];

        if ($item['Info']['durability'] > 0) {
            $r = $item['Info']['price'] / 2 / $item['Info']['durability'];
            $p = $item['max_dura'] * $r;

            if ($item['max_dura'] > 0) {
                $r = $item['current_dura'] / $item['max_dura'];
            } else {
                $r = 0;
            }

            $p = $p / 2 + ($p / 2) * $r + $item['Info']['price'] / 2;
        }

        $v = $item['ac'] + $item['mac'] + $item['dc'] + $item['mc'] + $item['sc'] + $item['accuracy'] + $item['agility'] + $item['hp'] + $item['mp'];
        $v += $item['attack_speed'] + $item['luck'] + $item['strong'] + $item['magic_resist'] + $item['poison_resist'] + $item['health_recovery'];
        $v += $item['mana_recovery'] + $item['poison_recovery'] + $item['critical_rate'] + $item['critical_damage'] + $item['freezing'] + $item['poison_attack'];
        $v = $v * 0.1 + 1;
        $p = $p * $v;

        return intval($p * $item['count']);
    }
}
