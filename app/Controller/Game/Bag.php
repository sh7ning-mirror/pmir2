<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Bag extends AbstractController
{
    public function bagLoadFromDB($character_id, $type, $num)
    {
        for ($i = 0; $i < $num; $i++) {
            $Items[$i] = [];
        }

        $bag = [
            'Player' => null,
            'Type'   => $type,
            'Items'  => $Items,
        ];

        $where = [
            'whereInfo' => [
                'where' => [
                    ['character_id', '=', $character_id],
                    ['type', '=', $type],
                ],
            ],
            'pageInfo'  => false,
        ];
        $res = $this->CommonService->getList('character_user_item', $where);
        if ($res['list']) {

            $ids        = [];
            $uItemIndex = [];
            foreach ($res['list'] as $k => $v) {
                $ids[]                          = $v['user_item_id'];
                $uItemIndex[$v['user_item_id']] = $v['index'];
            }

            $where = [
                'whereInfo' => [
                    'whereIn' => [
                        ['id', $ids],
                    ],
                ],
                'pageInfo'  => false,
            ];

            $res = $this->CommonService->getList('user_item', $where);

            if ($res['list']) {
                foreach ($res['list'] as $k => $v) {
                    $v['Info']                           = $this->GameData->getItemInfoByID($v['item_id']);
                    $v['dura_changed']                   = false;
                    $bag['Items'][$uItemIndex[$v['id']]] = $v;
                }
            }
        }

        return $bag;
    }

    public function useCount($Inventory, $i, $count)
    {
        return $this->setCount($Inventory, $i, $Inventory['Items'][$i]['count'] - $count);
    }

    public function setCount($Inventory, $i, $count)
    {
        if ($count == 0) {
            $Inventory = $this->set($Inventory, $i, null);
        } else {

            $where = [
                'whereInfo' => [
                    'where' => [
                        'id' => $Inventory['Items'][$i]['id'],
                    ],
                ],
            ];

            $data = [
                'count' => $count,
            ];

            $this->CommonService->upField('user_item', $where, $data);

            $Inventory['Items'][$i]['count'] = $count;
        }

        return $Inventory;
    }

    public function set($character_id, $Inventory, $i, $item = null)
    {
        if ($item) {
            if ($Inventory['Items'][$i]['isset']) {
                EchoLog('该位置有物品了', 'w');
            }

            if(empty($item['Info']['id']))
            {
                return false;
            }

            $info = [
                'item_id'         => $item['Info']['id'],
                'current_dura'    => 100,
                'max_dura'        => 100,
                'count'           => !empty($item['count']) ? $item['count'] : 1,
                'ac'              => $item['Info']['min_ac'],
                'mac'             => $item['Info']['min_mac'],
                'dc'              => $item['Info']['min_dc'],
                'mc'              => $item['Info']['min_mc'],
                'sc'              => $item['Info']['min_sc'],
                'accuracy'        => $item['Info']['accuracy'],
                'agility'         => $item['Info']['agility'],
                'hp'              => $item['Info']['hp'],
                'mp'              => $item['Info']['mp'],
                'attack_speed'    => $item['Info']['attack_speed'],
                'luck'            => $item['Info']['luck'],
                'soul_bound_id'   => $character_id,
                'bools'           => $item['Info']['bools'],
                'strong'          => $item['Info']['strong'],
                'magic_resist'    => $item['Info']['magic_resist'],
                'poison_resist'   => $item['Info']['poison_resist'],
                'health_recovery' => $item['Info']['health_recovery'],
                'mana_recovery'   => 0,
                'poison_recovery' => $item['Info']['poison_recovery'],
                'critical_rate'   => $item['Info']['critical_rate'],
                'critical_damage' => $item['Info']['critical_damage'],
                'freezing'        => $item['Info']['freezing'],
                'poison_attack'   => $item['Info']['poison_attack'],
            ];

            $res = $this->CommonService->save('user_item', $info);

            if ($res['code'] == 2000) {
                $info = [
                    'character_id' => $character_id,
                    'user_item_id' => $res['data']['id'],
                    'type'         => $this->Enum::UserItemTypeInventory,
                    'index'        => $i,
                ];

                $this->CommonService->save('character_user_item', $info);
            }

            $Inventory['Items'][$i]          = $item;
            $Inventory['Items'][$i]['isset'] = true;

            return $Inventory;
        } else {
            $item = $Inventory['Items'][$i];

            if ($item) {
                $where = [
                    'whereInfo' => [
                        'where' => [
                            'id' => $item['id'],
                        ],
                    ],
                ];

                $this->CommonService->delTrue('user_item', $where);

                $where = [
                    'whereInfo' => [
                        'where' => [
                            'user_item_id' => $item['id'],
                        ],
                    ],
                ];

                $this->CommonService->delTrue('character_user_item', $where);

            } else {
                EchoLog('尝试删除空位置的物品', 'w');
            }

            $Inventory['Items'][$i] = ['isset' => false];

            return $Inventory;
        }
    }

    public function moveTo(&$bag, $from, $to, &$tobag)
    {
        if ($from < 0 || $to < 0 || $from > count($bag['Items']) || $to > count($tobag['Items'])) {
            EchoLog(sprintf('移动装备位置不存在: from=%s to=%s', $from, $to), 'e');
            return false;
        }

        $item = $bag['Items'][$from] ?? [];

        if (!$item) {
            EchoLog(sprintf('背包格子 %s 没有物品', $from), 'e');
            return false;
        }

        co(function () use ($item, $tobag, $bag, $from, $to) {
            $where = [
                'whereInfo' => [
                    'where' => [
                        ['user_item_id', '=', $item['id']],
                    ],
                ],
            ];

            $data = [
                'type'  => $tobag['Type'],
                'index' => $to,
            ];

            $this->CommonService->upField('character_user_item', $where, $data);

            $toItem = $tobag['Items'][$to] ?? [];
            if (!$toItem) {
                $where = [
                    'whereInfo' => [
                        'where' => [
                            ['user_item_id', '=', $toItem['id']],
                        ],
                    ],
                ];

                $data = [
                    'type'  => $bag['Type'],
                    'index' => $from,
                ];

                $this->CommonService->upField('character_user_item', $where, $data);
            }
        });

        list($bag['Items'][$from], $tobag['Items'][$to]) = [$tobag['Items'][$to], $bag['Items'][$from]];

        return true;
    }

    public function move(&$bag, $from, $to)
    {
        return $this->moveTo($bag, $from, $to, $bag);
    }
}
