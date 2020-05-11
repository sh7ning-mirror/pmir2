<?php
namespace App\Controller\Game;

/**
 *
 */
class Bag
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

        $CommonService = getObject('CommonService');
        $res           = $CommonService->getList('character_user_item', $where);
        if ($res['list']) {

            $ids                = [];
            $userItemIDIndexMap = [];
            foreach ($res['list'] as $k => $v) {
                $ids[]                                  = $v['user_item_id'];
                $userItemIDIndexMap[$v['user_item_id']] = $v['index'];
            }

            $where = [
                'whereInfo' => [
                    'whereIn' => [
                        ['id', $ids],
                    ],
                ],
                'pageInfo'  => false,
            ];

            $res = $CommonService->getList('user_item', $where);

            if ($res['list']) {
                foreach ($res['list'] as $k => $v) {
                    $v['Info']                                   = getObject('GameData')->getItemInfoByID($v['item_id']);
                    $v['dura_changed']                           = false;
                    $bag['Items'][$userItemIDIndexMap[$v['id']]] = $v;
                }
            }
        }

        return $bag;
    }

    public function setCount($Inventory, $i, $count)
    {
        if ($count == 0) {
            $Inventory = $this->set($Inventory, $i, null);
        } else {
            $where = [
                'whereInfo' => [
                    'where' => [
                        'id' => $item['id'],
                    ],
                ],
            ];

            $data = [
                'count' => $count,
            ];

            getObject('CommonService')->upField('user_item', $where, $data);

            $Inventory['Items'][$i]['count'] = $count;
        }

        return $Inventory;
    }

    public function set($Inventory, $i, $item = null)
    {
        if (!$item) {
            if (!$Inventory['Items'][$i]) {
                EchoLog('尝试删除空位置的物品', 'e');
            }

            return $Inventory;
            //TODO
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
                $CommonService = getObject('CommonService');
                $CommonService->delTrue('user_item', $where);

                $where = [
                    'whereInfo' => [
                        'where' => [
                            'user_item_id' => $item['id'],
                        ],
                    ],
                ];

                $CommonService->delTrue('character_user_item', $where);

            } else {
                EchoLog('尝试删除空位置的物品', 'e');
            }

            $Inventory['Items'][$i] = null;

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
            $CommonService = getObject('CommonService');
            $where         = [
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

            $CommonService->upField('character_user_item', $where, $data);

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

                $CommonService->upField('character_user_item', $where, $data);
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
