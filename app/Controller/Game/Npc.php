<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Npc extends AbstractController
{
    public static $itemNameInfoMap;

    public function newNpc($map_id, $id, $npc)
    {
        $sc = $this->Script->loadFile($npc['file_name'] . '.txt');

        if (!$sc) {
            EchoLog(sprintf('NPC脚本加载失败: [%s] [%s]', $npc['chinese_name'], $npc['file_name'] . '.txt'), 'e');
            return false;
        }

        $npc = [
            // 'MapObject' => [
            'ID'              => $id,
            'Name'            => $npc['chinese_name'],
            'NameColor'       => ['R' => 0, 'G' => 255, 'B' => 0],
            'Map'             => $map_id,
            'CurrentLocation' => ['X' => $npc['location_x'], 'Y' => $npc['location_y']],
            'Direction'       => rand(0, 1),
            "Dead"            => false,
            "PlayerCount"     => 0,
            'InSafeZone'      => false,
            // ],
            'Image'           => $npc['image'],
            'Light'           => 0, // TODO
            'TurnTime'        => time(),
            'Script'          => $sc,
            'Goods'           => [],
            'BuyBack'         => [],
        ];

        if (!empty($npc['Script']['Goods'])) {
            if (!self::$itemNameInfoMap) {
                self::$itemNameInfoMap = json_decode($this->Redis->get('itemNameInfoMap'), true);
            }

            foreach ($npc['Script']['Goods'] as $name) {

                $res = explode(' ', $name);

                $name  = $res[0];
                $count = 1;
                if (count($res) == 2) {
                    $count = (int) $res[1];
                }

                $item = !empty(self::$itemNameInfoMap[$name]) ? self::$itemNameInfoMap[$name] : [];

                if (!$item) {
                    continue;
                }

                $g = $this->MsgFactory->newUserItem($item, $this->Atomic->newObjectID());

                $g['count']     = $count;
                $npc['Goods'][] = $g;
            }
        }

        // file_put_contents('/tmp/test.log', json_encode($npc,JSON_UNESCAPED_UNICODE),FILE_APPEND);
        return $npc;
    }

    public function callScript($p, $npc, $key)
    {
        return $this->Script->call($key, $npc, $p);
    }

    public function priceRate($p, $npc, $baseRate)
    {
        # code...
    }

    public function getPlayerBuyBack($p, $npc)
    {
        $key = 'npc:buyBack_' . $npc['ID'];

        $buyBackList = json_decode($this->Redis->get($key),true);

        return !empty($buyBackList[$p['ID']]) ? $buyBackList[$p['ID']] : [];
    }

    public function buy($p, $npc, $userItemID, $count)
    {
        $userItem  = [];
        $iter      = [];
        $isBuyBack = false;

        $items = $this->getPlayerBuyBack($p, $npc);

        if ($items) {
            foreach ($items as $k => $v) 
            {
                if ($v['id'] == $userItemID) {
                    $userItem  = $v;
                    $isBuyBack = true;
                    break;
                }
            }
        }

        if (!$isBuyBack) {
            $userItem = $this->getUserItemByID($npc, $userItemID);
        }

        if (!$userItem || !$count || $count > $userItem['Info']['stack_size']) {
            return false;
        }
        $price = $this->Item->price($userItem);

        if ($price > $p['Gold']) {
            return;
        }

        if ($isBuyBack) {
            $userItem['Info']['count'] = $count;
            $this->removeBuyBack($npc, $p, $userItemID);

            $this->PlayerObject->sendBuyBackGoods($p, $npc, false);
        } else {
            $userItem                  = $this->MsgFactory->newUserItem($userItem['Info'], $this->Atomic->newObjectID());
            $userItem['Info']['count'] = $count;
        }

        if ($this->PlayerObject->gainItem($p, $userItem['Info'])) {
            $this->PlayerObject->takeGold($p, $price);
        }
    }

    public function addBuyBack($npc, $p, $temp)
    {
        co(function () use ($npc, $p, $temp) {

            $key = 'npc:buyBack_' . $npc['ID'];

            $buyBackList = json_decode($this->Redis->get($key),true);

            $timeExpire         = 3 * 60 * 60; //过期时间,过期后放入商店 TODO 需要做定时器
            $temp['timeExpire'] = $timeExpire;

            $buyBackList[$p['ID']][] = $temp;
            
            $this->Redis->set($key, json_encode($buyBackList, JSON_UNESCAPED_UNICODE));
        });
    }

    public function removeBuyBack($npc, $p, $userItemID)
    {
        // co(function () use ($npc, $p, $userItemID) {
            $key = 'npc:buyBack_' . $npc['ID'];

            $buyBackList = json_decode($this->Redis->get($key),true);


            foreach ($buyBackList[$p['ID']] as $k => $v) 
            {
                if($v['id'] == $userItemID)
                {
                    unset($buyBackList[$p['ID']][$k]);
                    break;
                }
            }

            $this->Redis->set($key, json_encode($buyBackList, JSON_UNESCAPED_UNICODE));
        // });
    }

    public function getUserItemByID($npc, $id)
    {
        foreach ($npc['Goods'] as $key => $v) {
            if ($v['id'] == $id) {
                return $v;
            }
        }
    }

    public function hasType($npc, $type)
    {
        if (!empty($npc['Script']['Types'])) {
            foreach ($npc['Script']['Types'] as $k => $v) {
                if ($v == $type) {
                    return true;
                }
            }
        }
    }
}
