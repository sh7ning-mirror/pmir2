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
            'id'   => $this->Atomic->newObjectID(),
            'map'  => $map,
            'gold' => $gold,
        ];
    }

    public function newItem($map, $item)
    {
        $newItem = [
            'gold'      => 0,
            'user_item' => $item,
            'name'      => $item['info']['name'],
            'id'        => $this->Atomic->newObjectID(),
            'map'       => $map,
        ];

        if ($item['info']['grade'] == $this->Enum::ItemGradeNone) {
            $newItem['name_color'] = $this->Enum::ColorWhite;
        }

        if ($item['info']['grade'] == $this->Enum::ItemGradeCommon) {
            $newItem['name_color'] = $this->Enum::ColorWhite;
        }

        if ($item['info']['grade'] == $this->Enum::ItemGradeRare) {
            $newItem['name_color'] = $this->Enum::ColorDeepSkyBlue;
        }

        if ($item['info']['grade'] == $this->Enum::ItemGradeLegendary) {
            $newItem['name_color'] = $this->Enum::ColorDarkOrange;
        }

        if ($item['info']['grade'] == $this->Enum::ItemGradeMythical) {
            $newItem['name_color'] = $this->Enum::ColorPlum;
        }

        return $newItem;
    }

    public function drop($item, $center, $distance)
    {
        $mapInfo = $this->GameData->getMap($item['map']['info']['id']);
        $ok      = $this->Map->rangeCell($item, $mapInfo, $center, $distance, function ($item, $map_id, $x, $y) {

            $point = ['x' => $x, 'y' => $y];
            $ishas = $this->GameData->getMapItem($map_id, $point);

            if ($ishas) {
                return true;
            }

            $item['current_location'] = $point;
            $this->Map->addObject($item, $this->Enum::ObjectTypeItem);
            var_dump(json_encode($item));
            $this->Item->broadcastInfo($item);

            return false;
        });

        return $ok;
    }

    public function broadcastInfo($item)
    {
        if (!empty($item['user_item'])) {
            $this->broadcast($item, $this->MsgFactory->objectItem($item));
        } else {
            $this->broadcast($item, $this->MsgFactory->objectGold($item));
        }
    }

    public function broadcast($item, $msg)
    {
        $this->Map->broadcastP($item['current_location'], $msg, $item);
    }

    public function getImage($item)
    {
        $info = $item['user_item']['info'];

        switch ($info['type']) {
            case $this->Enum::ItemTypeAmulet:
                if ($info['stack_size'] > 0) {
                    switch ($info['shape']) {
                        case 0:
                            if ($item['user_item']['count'] >= 300) {
                                return 3662;
                            }

                            if ($item['user_item']['count'] >= 200) {
                                return 3661;
                            }

                            if ($item['user_item']['count'] >= 100) {
                                return 3660;
                            }

                            return 3660;
                            break;
                        case 1:
                            if ($item['user_item']['count'] >= 150) {
                                return 3675;
                            }

                            if ($item['user_item']['count'] >= 100) {
                                return 2960;
                            }

                            if ($item['user_item']['count'] >= 50) {
                                return 3674;
                            }

                            return 3673;
                            break;

                        case 2:
                            if ($item['user_item']['count'] >= 150) {
                                return 3672;
                            }

                            if ($item['user_item']['count'] >= 100) {
                                return 2961;
                            }

                            if ($item['user_item']['count'] >= 50) {
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

    public function getRealItem($origin, $level, $job, $itemList)
    {
        if (!empty($origin['class_based']) && !empty($origin['level_based'])) {
            return $this->getClassAndLevelBasedItem($origin, $job, $level, $itemList);
        }
        if (!empty($origin['class_based'])) {
            return $this->getClassBasedItem($origin, $job, $itemList);
        }
        if (!empty($origin['level_based'])) {
            return $this->getLevelBasedItem($origin, $level, $itemList);
        }

        return $origin;
    }

    public function getClassAndLevelBasedItem($origin, $job, $level, $itemList)
    {
        $output = $origin;

        for ($i = 0; $i < count($itemList); $i++) {
            $info = $this->GameData->getItemInfoById($itemList[$i]);
            if (strpos($info['name'], $origin['name']) === 0) {

                if ($info['required_class'] == (1 << $job)) {
                    if ($info['required_type'] == $this->Enum::RequiredTypeLevel && $info['required_amount'] <= $level && $output['required_amount'] <= $info['required_amount'] && $origin['required_gender'] == $info['required_gender']) {
                        $output = $info;
                    }
                }
            }
        }
        return $output;
    }

    public function getClassBasedItem($origin, $job, $itemList)
    {
        for ($i = 0; $i < count($itemList); $i++) {
            $info = $this->GameData->getItemInfoById($itemList[$i]);
            if (strpos($info['name'], $origin['name']) === 0) {
                if ($info['required_class'] == (1 << $job) && $origin['required_gender'] == $info['required_gender']) {
                    return $info;
                }
            }
        }
        return $origin;
    }

    public function getLevelBasedItem($origin, $level, $itemList)
    {
        $output = $origin;

        for ($i = 0; $i < count($itemList); $i++) {
            $info = $this->GameData->getItemInfoById($itemList[$i]);
            if (strpos($info['name'], $origin['name']) === 0) {
                if ($info['required_type'] == $this->Enum::RequiredTypeLevel && $info['required_amount'] <= $level && $output['required_amount'] < $info['required_amount'] && $origin['required_gender'] == $info['required_gender']) {
                    $output = $info;
                }
            }
        }
        return $output;
    }

    //物品价格
    public function price($item)
    {
        if (empty($item['info'])) {
            return 0;
        }

        $p = $item['info']['price'];

        if ($item['info']['durability'] > 0) {
            $r = $item['info']['price'] / 2 / $item['info']['durability'];
            $p = $item['max_dura'] * $r;

            if ($item['max_dura'] > 0) {
                $r = $item['current_dura'] / $item['max_dura'];
            } else {
                $r = 0;
            }

            $p = $p / 2 + ($p / 2) * $r + $item['info']['price'] / 2;
        }

        $v = $item['ac'] + $item['mac'] + $item['dc'] + $item['mc'] + $item['sc'] + $item['accuracy'] + $item['agility'] + $item['hp'] + $item['mp'];
        $v += $item['attack_speed'] + $item['luck'] + $item['strong'] + $item['magic_resist'] + $item['poison_resist'] + $item['health_recovery'];
        $v += $item['mana_recovery'] + $item['poison_recovery'] + $item['critical_rate'] + $item['critical_damage'] + $item['freezing'] + $item['poison_attack'];
        $v = $v * 0.1 + 1;
        $p = $p * $v;

        return intval($p * $item['count']);
    }

    //修理费
    public function repairPrice($item)
    {
        if (empty($item['info']) || $item['info']['durability'] == 0) {
            return 0;
        }

        $p = $item['info']['price'];

        if ($item['info']['durability'] > 0) {
            $p = $p * (($item['ac'] + $item['mac'] + $item['dc'] + $item['mc'] + $item['sc'] + $item['accuracy'] + $item['agility'] + $item['hp'] + $item['mp'] + $item['attack_speed'] + $item['luck'] + $item['strong'] + $item['magic_resist'] + $item['poison_resist'] + $item['health_recovery'] + $item['mana_recovery'] + $item['poison_recovery'] + $item['critical_rate'] + $item['critical_damage'] + $item['freezing'] + $item['poison_attack']) * 0.1 + 1.0);
        }

        $cost = $p * $item['count'] - $this->price($item);

        return intval($cost * 2);
    }
}
