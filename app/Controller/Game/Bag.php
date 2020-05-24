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
            'player' => null,
            'type'   => $type,
            'items'  => $Items,
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
                    $v['info']                           = $this->GameData->getItemInfoByID($v['item_id']);
                    $v['dura_changed']                   = false;
                    $bag['items'][$uItemIndex[$v['id']]] = $v;
                }
            }
        }

        return $bag;
    }

    public function useCount(&$Inventory, $i, $count)
    {
        $this->setCount($Inventory, $i, $Inventory['items'][$i]['count'] - $count);
    }

    public function setCount(&$Inventory, $i, $count)
    {
        if ($count == 0) {
            $this->set($Inventory, $i, null);
        } else {

            $where = [
                'whereInfo' => [
                    'where' => [
                        'id' => $Inventory['items'][$i]['id'],
                    ],
                ],
            ];

            $data = [
                'count' => $count,
            ];

            $this->CommonService->upField('user_item', $where, $data);

            $Inventory['items'][$i]['count'] = $count;
        }
    }

    public function set($character_id, &$Inventory, $i, $item = null)
    {
        if ($item) {
            if ($Inventory['items'][$i]['isset']) {
                EchoLog('该位置有物品了', 'w');
            }

            if (empty($item['info']['id'])) {
                return false;
            }

            $info = [
                'item_id'         => $item['info']['id'],
                'current_dura'    => 100,
                'max_dura'        => 100,
                'count'           => !empty($item['count']) ? $item['count'] : 1,
                'ac'              => $item['info']['min_ac'],
                'mac'             => $item['info']['min_mac'],
                'dc'              => $item['info']['min_dc'],
                'mc'              => $item['info']['min_mc'],
                'sc'              => $item['info']['min_sc'],
                'accuracy'        => $item['info']['accuracy'],
                'agility'         => $item['info']['agility'],
                'hp'              => $item['info']['hp'],
                'mp'              => $item['info']['mp'],
                'attack_speed'    => $item['info']['attack_speed'],
                'luck'            => $item['info']['luck'],
                'soul_bound_id'   => $character_id,
                'bools'           => $item['info']['bools'],
                'strong'          => $item['info']['strong'],
                'magic_resist'    => $item['info']['magic_resist'],
                'poison_resist'   => $item['info']['poison_resist'],
                'health_recovery' => $item['info']['health_recovery'],
                'mana_recovery'   => 0,
                'poison_recovery' => $item['info']['poison_recovery'],
                'critical_rate'   => $item['info']['critical_rate'],
                'critical_damage' => $item['info']['critical_damage'],
                'freezing'        => $item['info']['freezing'],
                'poison_attack'   => $item['info']['poison_attack'],
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

            $Inventory['items'][$i]          = $item;
            $Inventory['items'][$i]['isset'] = true;
        } else {
            $item = $Inventory['items'][$i];

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

            $Inventory['items'][$i] = ['isset' => false];
        }
    }

    public function moveTo(&$bag, $from, $to, &$tobag)
    {
        if ($from < 0 || $to < 0 || $from > count($bag['items']) || $to > count($tobag['items'])) {
            EchoLog(sprintf('移动装备位置不存在: from=%s to=%s', $from, $to), 'e');
            return false;
        }

        $item = $bag['items'][$from] ?? [];

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
                'type'  => $tobag['type'],
                'index' => $to,
            ];

            $this->CommonService->upField('character_user_item', $where, $data);

            $toItem = $tobag['items'][$to] ?? [];
            if (!$toItem) {
                $where = [
                    'whereInfo' => [
                        'where' => [
                            ['user_item_id', '=', $toItem['id']],
                        ],
                    ],
                ];

                $data = [
                    'type'  => $bag['type'],
                    'index' => $from,
                ];

                $this->CommonService->upField('character_user_item', $where, $data);
            }
        });

        list($bag['items'][$from], $tobag['items'][$to]) = [$tobag['items'][$to], $bag['items'][$from]];

        return true;
    }

    public function move(&$bag, $from, $to)
    {
        return $this->moveTo($bag, $from, $to, $bag);
    }
}
