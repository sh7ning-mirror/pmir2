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

    public function getPlayerBuyBack($p)
    {
        # code...
    }

    public function buy($p, $npc, $userItemID, $count)
    {
        $userItem  = [];
        $iter      = [];
        $isBuyBack = false;

        $items = !empty($npc['BuyBack'][$p['ID']]) ? $npc['BuyBack'][$p['ID']] : '';

        if ($items) {

            if (!empty($items[$userItemID])) {
                $userItem  = $items[$userItemID];
                $isBuyBack = true;
            }
        }

        if (!$isBuyBack) {
            $userItem = $this->getUserItemByID($npc, $userItemID);
        }

        if (!$userItem || !$count || $count > $userItem['Info']['stack_size']) {
            return false;
        }

        if ($userItem['Info']['price'] > $p['Gold']) {
            return;
        }

        if ($isBuyBack) {
            $count = $userItem['Count'];
            $this->removeBuyBack($npc, $p, $userItemID);

            $this->PlayerObject->sendBuyBackGoods($p, $npc, false);
        } else {
            $userItem          = $this->MsgFactory->newUserItem($userItem['Info'], $this->Atomic->newObjectID());
            $userItem['Count'] = $count;
        }

        if ($this->PlayerObject->gainItem($p, $userItem['Info'])) {
            $this->PlayerObject->takeGold($p, $userItem['Info']['price']);
        }
    }

    public function removeBuyBack($npc, $p, $userItemID)
    {
        co(function () use ($npc, $p, $userItemID) {
            unset($npc['BuyBack'][$p['ID']][$userItemID]);
            $key = 'map:npcs_' . $npc['Map'];
            $this->Redis->set($key, $npc);
        });
    }

    public function getUserItemByID($npc, $id)
    {
        foreach ($npc['Goods'] as $key => $v) {
            if ($v['id'] == $id) {
                return $v;
            }
        }
    }
}
