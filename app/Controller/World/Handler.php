<?php
declare (strict_types = 1);

namespace App\Controller\World;

use App\Controller\AbstractController;

/**
 *
 */
class Handler extends AbstractController
{
    public function walk($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $point = $this->Point->NextPoint($p['current_location'], $param['res']['direction'], 1);

        $data = [
            'location'  => $point,
            'direction' => $param['res']['direction'],
        ];

        if ($this->PlayerObject->checkMovement($point, $p)) {
            return false;
        }

        if (!$this->Map->updateObject($p, $point, $this->Enum::ObjectTypePlayer)) {
            $p['current_location']  = $point;
            $p['current_direction'] = $param['res']['direction'];
            return ['USER_LOCATION', $data];
        }

        $p['current_location']  = $point;
        $p['current_direction'] = $param['res']['direction'];

        $this->PlayerObject->broadcast($p, ['OBJECT_WALK', $this->MsgFactory->objectWalk($p)]);

        return ['USER_LOCATION', $data];
    }

    public function run($fd, $param)
    {
        $steps = 2;

        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        for ($i = 1; $i <= $steps; $i++) {

            $point = $this->Point->NextPoint($p['current_location'], $param['res']['direction'], $i);
            $data  = [
                'location'  => $point,
                'direction' => $param['res']['direction'],
            ];

            if (!$this->Map->checkDoorOpen($p['map']['info']['id'], $point)) {
                $this->SendMsg->send($fd, ['USER_LOCATION', $data]);
                return false;
            }

            if ($this->PlayerObject->checkMovement($point, $p)) {
                return false;
            }
        }

        if (!$this->Map->updateObject($p, $point, $this->Enum::ObjectTypePlayer)) {
            $p['current_location']  = $point;
            $p['current_direction'] = $param['res']['direction'];
            return ['USER_LOCATION', $data];
        }

        $p['current_location']  = $point;
        $p['current_direction'] = $param['res']['direction'];

        $this->SendMsg->send($fd, ['USER_LOCATION', $data]);

        $this->PlayerObject->broadcast($p, ['OBJECT_RUN', $this->MsgFactory->objectRun($p)]);
    }

    public function turn($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $data = [
            'location'  => $p['current_location'],
            'direction' => $param['res']['direction'],
        ];

        $p['current_direction'] = $param['res']['direction'];

        $this->SendMsg->send($fd, ['USER_LOCATION', $data]);

        $this->PlayerObject->broadcast($p, ['OBJECT_TURN', $this->MsgFactory->objectTurn($p)]);
    }

    public function logOut($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        var_dump('_______________________________');
        var_dump($p['experience']);
        if (!$p || !isset($p['game_stage']) || $p['game_stage'] != $this->Enum::GAME) {
            return false;
        }

        $Characters = $this->Character->getAccountCharacters($p['account']);

        $this->PlayerObject->stopGame($p);

        $p['game_stage'] = $this->Enum::SELECT;
        
        co(function () use ($fd, $p) {

            //保存玩家属性
            $this->PlayersList->saveData($fd, $p);

            //删除玩家
            $this->Map->deleteObject($p);
        });

        return ['LOG_OUT_SUCCESS', ['count' => count($Characters), 'characters' => $Characters]];
    }

    public function gameOver($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        if (!$p || !isset($p['game_stage']) || $p['game_stage'] != $this->Enum::GAME) {
            return false;
        }

        $Characters = $this->Character->getAccountCharacters($p['account']);

        $this->PlayerObject->stopGame($p);

        co(function () use ($fd, $p) {

            //保存玩家属性
            $this->PlayersList->saveData($fd, $p);

            //删除玩家
            $this->Map->deleteObject($p);

            //删除账户数据
            $this->GameData->delPlayer($fd);
        });
    }

    public function chat($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        //私聊
        if (strpos($param['res']['message'], '/') === 0) {
            return false;
        }

        //小队
        if (strpos($param['res']['message'], '!!') === 0) {
            return false;
        }

        //行会
        if (strpos($param['res']['message'], '!~') === 0) {
            return false;
        }

        //师徒
        if (strpos($param['res']['message'], '!#') === 0) {
            return false;
        }

        //喊话
        if (strpos($param['res']['message'], '!') === 0) {
            return false;
        }

        //夫妻
        if (strpos($param['res']['message'], ':)') === 0) {
            return false;
        }

        //GM 喊话
        if (strpos($param['res']['message'], '@!') === 0) {
            return false;
        }

        //command 命令 TODO
        if (strpos($param['res']['message'], '@') === 0) {
            return false;
        }

        $msg = $this->MsgFactory->objectChat($p, $param['res']['message'], $this->Enum::ChatTypeNormal);

        $this->PlayerObject->broadcast($p, ['OBJECT_CHAT', $msg]);

        return ['OBJECT_CHAT', $msg];
    }

    public function openDoor($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $this->PlayerObject->openDoor($p, $param['res']['door_index']);
    }

    public function refineCancel($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        if (!empty($p['refine'])) {
            foreach ($p['refine'] as $temp) {
                if (!$temp) {
                    continue;
                }

                if (!empty($p['inventory'])) {
                    continue;
                }

                foreach ($p['inventory'] as $k => $v) {
                    # code...
                }
            }
        }

        // for (int t = 0; t < Info.Refine.Length; t++)
        // {
        //     UserItem temp = Info.Refine[t];

        //     if (temp == null) continue;

        //     for (int i = 0; i < Info.Inventory.Length; i++)
        //     {
        //         if (Info.Inventory[i] != null) continue;

        //         //Put item back in inventory
        //         if (CanGainItem(temp))
        //         {
        //             RetrieveRefineItem(t, i);
        //         }
        //         else //Drop item on floor if it can no longer be stored
        //         {
        //             if (DropItem(temp, Settings.DropRange))
        //             {
        //                 Enqueue(new S.DeleteItem { UniqueID = temp.UniqueID, Count = temp.Count });
        //             }
        //         }

        //         Info.Refine[t] = null;

        //         break;
        //     }
        // }
    }

    public function equipItem($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $msg = [
            'grid'      => $param['res']['grid'],
            'unique_id' => $param['res']['unique_id'],
            'to'        => $param['res']['to'],
            'success'   => false,
        ];

        $res = $this->PlayerObject->getUserItemByID($p, $param['res']['grid'], $param['res']['unique_id']);

        if ($res[0] === false) {
            return ['EQUIP_ITEM', $msg];
        }

        $Inventory = $p['inventory'];
        $Equipment = $p['equipment'];
        $Storage   = $p['storage'];

        switch ($param['res']['grid']) {
            case $this->Enum::MirGridTypeInventory:
                $err            = $this->Bag->moveTo($Inventory, $res[0], $msg['to'], $Equipment);
                $p['inventory'] = $Inventory;
                $p['equipment'] = $Equipment;
                break;

            case $this->Enum::MirGridTypeStorage:
                $err            = $this->Bag->moveTo($Inventory, $res[0], $msg['to'], $Storage);
                $p['inventory'] = $Inventory;
                $p['storage']   = $Storage;
                break;
        }

        if (!$err) {
            return ['EQUIP_ITEM', $msg];
        }

        $msg['success'] = true;
        $this->PlayerObject->refreshStats($p);

        $this->PlayerObject->updateConcentration($p);
        $this->PlayerObject->broadcast($p, ['PLAYER_UPDATE', $this->MsgFactory->playerUpdate($p)]);

        return ['EQUIP_ITEM', $msg];
    }

    public function removeItem($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $msg = [
            'grid'      => $param['res']['grid'],
            'unique_id' => $param['res']['unique_id'],
            'to'        => $param['res']['to'],
            'success'   => false,
        ];

        $res = $this->PlayerObject->getUserItemByID($p, $this->Enum::MirGridTypeEquipment, $param['res']['unique_id']);

        if ($res[0] === false) {
            return ['REMOVE_ITEM', $msg];
        }

        $Inventory = $p['inventory'];
        $Equipment = $p['equipment'];
        $Storage   = $p['storage'];

        switch ($param['res']['grid']) {
            case $this->Enum::MirGridTypeInventory:
                $this->Bag->moveTo($Equipment, $res[0], $msg['to'], $Inventory);
                $p['equipment'] = $Equipment;
                $p['inventory'] = $Inventory;
                break;

            case $this->Enum::MirGridTypeStorage:

                if (!$this->Util->stringEqualFold($p['calling_npc_page'], $this->Enum::StorageKey)) {
                    return ['REMOVE_ITEM', $msg];
                }

                $this->Bag->moveTo($Equipment, $res[0], $msg['to'], $Storage);
                $p['equipment'] = $Equipment;
                $p['storage']   = $Storage;
                break;
        }

        $msg['success'] = true;
        $this->PlayerObject->refreshStats($p);

        $this->PlayerObject->updateConcentration($p);
        $this->PlayerObject->broadcast($p, ['PLAYER_UPDATE', $this->MsgFactory->playerUpdate($p)]);

        return ['REMOVE_ITEM', $msg];
    }

    public function moveItem($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $msg = [
            'grid'    => $param['res']['grid'],
            'from'    => $param['res']['from'],
            'to'      => $param['res']['to'],
            'success' => false,
        ];

        $Inventory = $p['inventory'];
        $Storage   = $p['storage'];
        $Trade     = $p['trade'];

        switch ($param['res']['grid']) {
            case $this->Enum::MirGridTypeInventory:
                $err            = $this->Bag->move($Inventory, $msg['from'], $msg['to']);
                $p['inventory'] = $Inventory;
                break;

            case $this->Enum::MirGridTypeStorage:
                $err          = $this->Bag->move($Storage, $msg['from'], $msg['to']);
                $p['storage'] = $Storage;
                break;

            case $this->Enum::MirGridTypeTrade:
                $err        = $this->Bag->moveTo($Trade, $msg['from'], $msg['to']);
                $p['trade'] = $Trade;

                $this->PlayerObject->TradeItem();
                break;

            case $this->Enum::MirGridTypeRefine:
                // TODO
                break;
        }

        if (!$err) {
            $this->PlayerObject->receiveChat($p['fd'], '移动物品失败', $this->Enum::ChatTypeSystem);
        } else {
            $msg['success'] = true;
        }

        return ['MOVE_ITEM', $msg];
    }

    public function callNpc($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $defaultNpc = $this->GameData->getDefaultNpc();

        if ($param['res']['object_id'] == $defaultNpc['id']) {
            $npc = $defaultNpc;
        } else {
            $npc = $this->GameData->getMapNpcInfo($p['map']['info']['id'], $param['res']['object_id']);
        }

        if (!$npc) {
            EchoLog(sprintf('NPC不存在: %s %s', $param['res']['object_id'], $param['res']['key']), 'w');
            return false;
        }

        $this->PlayerObject->callNPC1($p, $npc, $param['res']['key']);
    }

    public function buyItem($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        if ($p['dead']) {
            return false;
        }

        if (!$this->Util->stringEqualFold($p['calling_npc_page'], [$this->Enum::BuySellKey, $this->Enum::BuyKey, $this->Enum::BuyBackKey, $this->Enum::BuyUsedKey, $this->Enum::PearlBuyKey])) {
            return false;
        }

        if (!$p['calling_npc']) {
            return false;
        }

        $npc = $this->GameData->getMapNpcInfo($p['map']['info']['id'], $p['calling_npc']);

        $this->Npc->buy($p, $npc, $param['res']['item_index'], $param['res']['count']);
    }

    public function dropItem($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        if ($p['dead']) {
            return false;
        }

        $res = $this->PlayerObject->getUserItemByID($p, $this->Enum::MirGridTypeInventory, $param['res']['unique_id']);
        if (!$res[1]) {
            return $this->MsgFactory->dropItem($param['res']['unique_id'], $param['res']['count'], false);
        }

        $item = $this->Item->newItem($p['map'], $res[1]);

        $dropMsg = $this->Item->drop($item, $p['current_location'], 1);

        if (!$dropMsg) {
            $this->PlayerObject->receiveChat($p['fd'], sprintf('坐标(%s)附近没有合适的点放置物品;', $p['current_location']), $this->Enum::ChatTypeSystem);

            return $this->MsgFactory->dropItem($param['res']['unique_id'], $param['res']['count'], false);
        }

        if ($param['res']['count'] >= $res[1]['count']) {
            $this->Bag->set($p['id'], $p['inventory'], $res[0], null);
        } else {
            $this->Bag->useCount($p['inventory'], $res[0], $param['res']['count']);
        }

        $this->PlayerObject->refreshBagWeight($p);

        return $this->MsgFactory->dropItem($param['res']['unique_id'], $param['res']['count'], true);
    }

    public function sellItem($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        if ($p['dead'] || !$param['res']['count']) {
            return $this->MsgFactory->sellItem($param['res']['unique_id'], $param['res']['count'], false);
        }

        if (!$this->Util->stringEqualFold($p['calling_npc_page'], [$this->Enum::BuySellKey, $this->Enum::SellKey])) {
            return $this->MsgFactory->sellItem($param['res']['unique_id'], $param['res']['count'], false);
        }

        $index = -1;
        $temp  = null;

        foreach ($p['inventory']['items'] as $k => $v) {
            if (!isset($v['isset']) || !$v['isset'] || $param['res']['unique_id'] != $v['id']) {
                continue;
            }

            $temp  = $v;
            $index = $k;
            break;
        }

        if (!$temp || $index == -1 || $param['res']['count'] > $temp['count']) {
            return $this->MsgFactory->sellItem($param['res']['unique_id'], $param['res']['count'], false);
        }

        if ($this->Util->hasFlagUint16($temp['info']['bind'], $this->Enum::BindModeDontSell)) {
            return $this->MsgFactory->sellItem($param['res']['unique_id'], $param['res']['count'], false);
        }

        $npc = $this->GameData->getMapNpcInfo($p['map']['info']['id'], $p['calling_npc']);
        if (!$this->Npc->hasType($npc, $temp['info']['type'])) {
            $this->PlayerObject->receiveChat($p['fd'], '不能在这里卖这类商品', $this->Enum::ChatTypeSystem);
            return $this->MsgFactory->sellItem($param['res']['unique_id'], $param['res']['count'], false);
        }

        if ($temp['info']['stack_size'] > 1 && $param['res']['count'] != $temp['count']) {
            $item          = $this->MsgFactory->newUserItem($temp['info'], $this->Atomic->newObjectID());
            $item['count'] = $param['res']['count'];

            if ($this->Item->price($item) + $p['gold'] > 18446744073709551615) {
                return $this->MsgFactory->sellItem($param['res']['unique_id'], $param['res']['count'], false);
            }

            $temp['count'] -= $param['res']['count'];
            $temp = $item;
        } else {
            $this->Bag->set($p['id'], $p['inventory'], $index, null);
        }

        $this->Npc->setPlayerBuyBack($npc, $p, $temp); //出售回购

        $this->PlayerObject->refreshBagWeight($p);

        $this->PlayerObject->gainGold($p, $this->Item->price($temp) / 2);

        return $this->MsgFactory->sellItem($param['res']['unique_id'], $param['res']['count'], true);
    }

    public function pickUp($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        if ($p['dead']) {
            return false;
        }

        $items = [];

        $item = $this->Map->getObjectByPoint($p, $this->Enum::ObjectTypeItem);

        if (!$item) {
            return false;
        }

        if (empty($item['user_item'])) {
            $this->PlayerObject->gainGold($p, $item['gold']);
            $items[] = $item;
        } else {
            $item['user_item']['info']['count'] = $item['user_item']['count'];
            if ($this->PlayerObject->gainItem($p, $item['user_item']['info'])) {
                $items[] = $item;
            }
        }

        foreach ($items as $key => $item) {
            $object = [
                'current_location' => $p['current_location'],
                'map'              => $p['map'],
                'id'               => $item['id'],
            ];

            $this->Map->deleteObject($object, $this->Enum::ObjectTypeItem);

            $this->Item->broadcast($object, $this->MsgFactory->objectRemove($item));
        }
    }

    public function changeAMode($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $p['a_mode'] = $param['res']['mode'];

        $this->SendMsg->send($p['fd'], $this->MsgFactory->changeAMode($param['res']['mode']));
    }

    public function changePMode($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $p['p_mode'] = $param['res']['mode'];

        $this->SendMsg->send($p['fd'], $this->MsgFactory->changePMode($param['res']['mode']));
    }

    public function useItem($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        if ($p['dead']) {
            return false;
        }

        $res = $this->PlayerObject->getUserItemByID($p, $this->Enum::MirGridTypeInventory, $param['res']['unique_id']);
        if (empty($res[1]) || $res[1]['id'] === 0 || !$this->PlayerObject->canUseItem($p, $res[1])) {
            return $this->MsgFactory->useItem($param['res']['unique_id'], false);
        }

        $info = $res[1]['info'];

        $msg = $this->MsgFactory->useItem($param['res']['unique_id'], false);

        switch ($info['type']) {
            case $this->Enum::ItemTypePotion:
                $msg[1]['success'] = $this->PlayerObject->userItemPotion($p, $res[1]);
                break;

            case $this->Enum::ItemTypeScroll:
                $msg[1]['success'] = $this->PlayerObject->useItemScroll($p, $res[1]);
                break;

            case $this->Enum::ItemTypeBook:
                $msg[1]['success'] = $this->PlayerObject->giveSkill($p, $info['shape'], 1);
                break;

            case $this->Enum::ItemTypeScript:
                $this->PlayerObject->callDefaultNPC($p, $this->Enum::DefaultNPCTypeUseItem, $info['shape']);
                $msg[1]['success'] = true;
                break;

            case $this->Enum::ItemTypeFood:
                # code...
                break;

            case $this->Enum::ItemTypePets:
                # code...
                break;

            case $this->Enum::ItemTypeTransform:
                # code...
                break;
        }

        if ($msg[1]['success']) {
            if ($res[1]['count'] > 1) {
                $this->Bag->useCount($p['inventory'], $res[0], 1);
            } else {
                $this->Bag->set($p['id'], $p['inventory'], $res[0], null);
            }

            $this->PlayerObject->refreshBagWeight($p);

            return $msg;
        }
    }

    public function dropGold($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        if ($p['dead']) {
            return false;
        }

        if ($p['gold'] < $param['res']['amount']) {
            return false;
        }

        $item = $this->Item->newGold($p['map'], $param['res']['amount']);

        $dropMsg = $this->Item->drop($item, $p['current_location'], 3);

        if (!$dropMsg) {
            $this->PlayerObject->receiveChat($p['fd'], sprintf('坐标(%s)附近没有合适的点放置物品;', $p['current_location']), $this->Enum::ChatTypeSystem);
            return false;
        }

        $this->PlayerObject->takeGold($p, $param['res']['amount']);
    }

    public function repairItem($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $this->PlayerObject->repairItem($p, $param['res']['unique_id'], false);
    }

    public function requestUserName($fd, $param)
    {
        $p = $this->PlayerObject->getIdPlayer($param['res']['user_id']);

        $p = &$this->PlayerData::$players[$p['fd']];

        if ($p) {
            return $this->MsgFactory->userName($param['res']['user_id'], $p['name']);
        }
    }

    public function attack($fd, $param)
    {
        // $p = $this->PlayerObject->getPlayer($fd);
        $p = &$this->PlayerData::$players[$fd];

        $this->PlayerObject->attack($p, $param['res']['direction'], $param['res']['spell']);
    }
}
