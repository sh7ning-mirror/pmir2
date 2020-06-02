<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Npc extends AbstractController
{
    public function newNpc($map, $id, $npc)
    {
        $sc = $this->Script->loadFile($npc['file_name'] . '.txt');

        if (!$sc) {
            EchoLog(sprintf('NPC脚本加载失败: [%s] [%s]', $npc['chinese_name'], $npc['file_name'] . '.txt'), 'e');
            return false;
        }

        $npc = [
            'id'               => $id,
            'name'             => $npc['chinese_name'],
            'name_color'       => ['r' => 0, 'g' => 255, 'b' => 0],
            'map'              => $map ? [
                'id'     => $map['info']['id'],
                'width'  => !empty($map['width']) ? $map['width'] : '',
                'height' => !empty($map['height']) ? $map['height'] : '',
                'info'   => [
                    'id' => $map['info']['id'],
                ],
            ] : '',
            'current_location' => ['x' => $npc['location_x'], 'y' => $npc['location_y']],
            'direction'        => rand(0, 1),
            'dead'             => false,
            'player_count'     => 0,
            'in_safe_zone'     => false,
            'image'            => $npc['image'],
            'light'            => 0, // TODO
            'turn_time'        => time(),
            'script'           => $sc,
            'goods'            => [],
            'buy_back'         => [],
        ];

        if (!empty($npc['script']['goods'])) {
            foreach ($npc['script']['goods'] as $name) {

                $res = explode(' ', $name);

                $name  = $res[0];
                $count = 1;
                if (count($res) == 2) {
                    $count = (int) $res[1];
                }

                $item = !empty($this->ItemData::$itemNameInfos[$name]) ? $this->ItemData::$itemNameInfos[$name] : [];

                if (!$item) {
                    continue;
                }

                $g = $this->MsgFactory->newUserItem($item, $this->Atomic->newObjectID());

                $g['count']     = $count;
                $npc['goods'][] = $g;
            }
        }

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

    public function buy($p, $npc, $userItemID, $count)
    {
        $userItem  = [];
        $iter      = [];
        $isBuyBack = false;

        $items = $this->GameData->getPlayerBuyBack($p['id'], $npc['id']);

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
            $this->removePlayerBuyBack($npc, $p, $userItemID);

            $this->PlayerObject->sendBuyBackGoods($p, $npc, false);
        } else {
            $userItem                  = $this->MsgFactory->newUserItem($userItem['info'], $this->Atomic->newObjectID());
            $userItem['info']['count'] = $count;
        }

        if ($this->PlayerObject->gainItem($p, $userItem['info'])) {
            $this->PlayerObject->takeGold($p, $price);
        }
    }

    public function setPlayerBuyBack($npc, $p, $temp)
    {
        co(function () use ($npc, $p, $temp) {
            $temp['time_expire'] = time() + (3 * 60 * 60); //过期时间,过期后放入商店

            $this->GameData->setPlayerBuyBack($p['id'], $npc['id'], $temp);
        });
    }

    public function removePlayerBuyBack($npc, $p, $userItemID)
    {
        $this->GameData->removePlayerBuyBack($p['id'], $npc['id'], $userItemID);
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

    public function broadcast($npcInfo, $msg)
    {
        $this->Map->broadcastP($npcInfo['current_location'], $msg, $npcInfo);
    }

    public function process(&$npcInfo)
    {
        $time = time();
        if ($npcInfo['turn_time'] < $time) {
            $npcInfo['turn_time'] = $time + rand(20, 60) * 60 * 60;
            $npcInfo['direction'] = rand(0, 1);
            $npcInfo['current_direction'] = $npcInfo['direction'];
            $this->broadcast($npcInfo, ['OBJECT_TURN', $this->MsgFactory->objectTurn($npcInfo)]);
        }

        //回购物品加入商店
        $buyBack = $this->GameData->getNpcBuyBack($npcInfo['id']);
        if($buyBack)
        {
            foreach ($buyBack as $k => $v)
            {
                if($v['time_expire'] <= $time)
                {

                }
            }
        }
    }
}
