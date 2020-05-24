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
            'id'               => $id,
            'name'             => $npc['chinese_name'],
            'name_color'       => ['r' => 0, 'g' => 255, 'b' => 0],
            'map'              => $map_id,
            'current_location' => ['x' => $npc['location_x'], 'y' => $npc['location_y']],
            'direction'        => rand(0, 1),
            'dead'             => false,
            'player_count'     => 0,
            'in_safe_zone'     => false,
            // ],
            'image'            => $npc['image'],
            'light'            => 0, // TODO
            'turn_time'        => time(),
            'script'           => $sc,
            'goods'            => [],
            'buy_back'         => [],
        ];

        if (!empty($npc['script']['goods'])) {
            if (!self::$itemNameInfoMap) {
                self::$itemNameInfoMap = json_decode($this->Redis->get('itemNameInfoMap'), true);
            }

            foreach ($npc['script']['goods'] as $name) {

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
                $npc['goods'][] = $g;
            }
        }

        // file_put_contents('/tmp/test.log', json_encode($npc,JSON_UNESCAPED_UNICODE),FILE_APPEND);
        return $npc;
    }

    public function callScript(&$p, $npc, $key)
    {
        return $this->Script->call($key, $npc, $p);
    }

    public function priceRate($p, $npc, $baseRate)
    {
        return $npc['info']['rate'] / 100;
    }

    public function getPlayerBuyBack($p, $npc)
    {
        $key = 'npc:buyBack_' . $npc['id'];

        $buyBackList = json_decode($this->Redis->get($key), true);

        return !empty($buyBackList[$p['id']]) ? $buyBackList[$p['id']] : [];
    }

    public function buy($p, $npc, $userItemID, $count)
    {
        $userItem  = [];
        $iter      = [];
        $isBuyBack = false;

        $items = $this->getPlayerBuyBack($p, $npc);

        if ($items) {
            foreach ($items as $k => $v) {
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

        if (!$userItem || !$count || $count > $userItem['info']['stack_size']) {
            return false;
        }
        $price = $this->Item->price($userItem);

        if ($price > $p['gold']) {
            return;
        }

        if ($isBuyBack) {
            $userItem['info']['count'] = $count;
            $this->removeBuyBack($npc, $p, $userItemID);

            $this->PlayerObject->sendBuyBackGoods($p, $npc, false);
        } else {
            $userItem                  = $this->MsgFactory->newUserItem($userItem['info'], $this->Atomic->newObjectID());
            $userItem['info']['count'] = $count;
        }

        if ($this->PlayerObject->gainItem($p, $userItem['info'])) {
            $this->PlayerObject->takeGold($p, $price);
        }
    }

    public function addBuyBack($npc, $p, $temp)
    {
        co(function () use ($npc, $p, $temp) {

            $key = 'npc:buyBack_' . $npc['id'];

            $buyBackList = json_decode($this->Redis->get($key), true);

            $timeExpire          = 3 * 60 * 60; //过期时间,过期后放入商店 TODO 需要做定时器
            $temp['time_expire'] = $timeExpire;

            $buyBackList[$p['id']][] = $temp;

            $this->Redis->set($key, json_encode($buyBackList, JSON_UNESCAPED_UNICODE));
        });
    }

    public function removeBuyBack($npc, $p, $userItemID)
    {
        // co(function () use ($npc, $p, $userItemID) {
        $key = 'npc:buyBack_' . $npc['id'];

        $buyBackList = json_decode($this->Redis->get($key), true);

        foreach ($buyBackList[$p['id']] as $k => $v) {
            if ($v['id'] == $userItemID) {
                unset($buyBackList[$p['id']][$k]);
                break;
            }
        }

        $this->Redis->set($key, json_encode($buyBackList, JSON_UNESCAPED_UNICODE));
        // });
    }

    public function getUserItemByID($npc, $id)
    {
        foreach ($npc['goods'] as $key => $v) {
            if ($v['id'] == $id) {
                return $v;
            }
        }
    }

    public function hasType($npc, $type)
    {
        if (!empty($npc['script']['types'])) {
            foreach ($npc['script']['types'] as $k => $v) {
                if ($v == $type) {
                    return true;
                }
            }
        }
    }
}
