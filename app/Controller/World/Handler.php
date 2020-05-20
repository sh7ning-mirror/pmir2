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
        $p = $this->PlayerObject->getPlayer($fd);

        $point = $this->Point->NextPoint($p['CurrentLocation'], $param['res']['Direction'], 1);

        $data = [
            'Location'  => $point,
            'Direction' => $param['res']['Direction'],
        ];

        if ($this->PlayerObject->checkMovement($point, $p)) {
            $this->PlayerObject->setPlayer($fd, $p);
            return false;
        }

        if (!$this->Map->updateObject($p, $point, $this->Enum::ObjectTypePlayer)) {
            $p['CurrentLocation']  = $point;
            $p['CurrentDirection'] = $param['res']['Direction'];
            $this->PlayerObject->setPlayer($fd, $p);
            return ['USER_LOCATION', $data];
        }

        $p['CurrentLocation']  = $point;
        $p['CurrentDirection'] = $param['res']['Direction'];
        $this->PlayerObject->setPlayer($fd, $p);

        $this->PlayerObject->broadcast($p, ['OBJECT_WALK', $this->MsgFactory->objectWalk($p)]);

        return ['USER_LOCATION', $data];
    }

    public function run($fd, $param)
    {
        $steps = 2;

        $p = $this->PlayerObject->getPlayer($fd);

        for ($i = 1; $i <= $steps; $i++) {

            $point = $this->Point->NextPoint($p['CurrentLocation'], $param['res']['Direction'], $i);
            $data  = [
                'Location'  => $point,
                'Direction' => $param['res']['Direction'],
            ];

            if (!$this->Map->checkDoorOpen($p['Map']['Info']['id'], $point)) {
                $this->PlayerObject->setPlayer($fd, $p);
                $this->SendMsg->send($fd, ['USER_LOCATION', $data]);
                return false;
            }

            if ($this->PlayerObject->checkMovement($point, $p)) {
                $this->PlayerObject->setPlayer($fd, $p);
                return false;
            }
        }

        if (!$this->Map->updateObject($p, $point, $this->Enum::ObjectTypePlayer)) {
            $p['CurrentLocation']  = $point;
            $p['CurrentDirection'] = $param['res']['Direction'];
            $this->PlayerObject->setPlayer($fd, $p);
            return ['USER_LOCATION', $data];
        }

        $p['CurrentLocation']  = $point;
        $p['CurrentDirection'] = $param['res']['Direction'];
        $this->PlayerObject->setPlayer($fd, $p);

        $this->SendMsg->send($fd, ['USER_LOCATION', $data]);

        $this->PlayerObject->broadcast($p, ['OBJECT_RUN', $this->MsgFactory->objectRun($p)]);
    }

    public function turn($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        $data = [
            'Location'  => $p['CurrentLocation'],
            'Direction' => $param['res']['Direction'],
        ];

        $p['CurrentDirection'] = $param['res']['Direction'];

        $this->SendMsg->send($fd, ['USER_LOCATION', $data]);

        $this->PlayerObject->setPlayer($fd, $p);

        $this->PlayerObject->broadcast($p, ['OBJECT_TURN', $this->MsgFactory->objectTurn($p)]);
    }

    public function logOut($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        if (!$p || !isset($p['GameStage']) || $p['GameStage'] != $this->Enum::GAME) {
            return false;
        }

        $Characters = $this->Character->getAccountCharacters($p['account']);

        $this->PlayerObject->stopGame($p);

        co(function () use ($fd, $p) {

            $p['GameStage'] = $this->Enum::SELECT;

            $this->PlayerObject->setPlayer($fd, $p);

            //保存玩家属性
            $this->PlayersList->saveData($fd, $p);

            //删除玩家
            $this->PlayersList->delPlayersList($fd, $p);
        });

        return ['LOG_OUT_SUCCESS', ['Count' => count($Characters), 'Characters' => $Characters]];
    }

    public function chat($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        //私聊
        if (strpos($param['res']['Message'], '/') === 0) {
            return false;
        }

        //小队
        if (strpos($param['res']['Message'], '!!') === 0) {
            return false;
        }

        //行会
        if (strpos($param['res']['Message'], '!~') === 0) {
            return false;
        }

        //师徒
        if (strpos($param['res']['Message'], '!#') === 0) {
            return false;
        }

        //喊话
        if (strpos($param['res']['Message'], '!') === 0) {
            return false;
        }

        //夫妻
        if (strpos($param['res']['Message'], ':)') === 0) {
            return false;
        }

        //GM 喊话
        if (strpos($param['res']['Message'], '@!') === 0) {
            return false;
        }

        //command 命令 TODO
        if (strpos($param['res']['Message'], '@') === 0) {
            return false;
        }

        $msg = $this->MsgFactory->objectChat($p, $param['res']['Message'], $this->Enum::ChatTypeNormal);

        $this->PlayerObject->broadcast($p, ['OBJECT_CHAT', $msg]);

        return ['OBJECT_CHAT', $msg];
    }

    public function openDoor($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        $this->PlayerObject->openDoor($p, $param['res']['DoorIndex']);
    }

    public function refineCancel($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        if (!empty($p['Refine'])) {
            foreach ($p['Refine'] as $temp) {
                if (!$temp) {
                    continue;
                }

                if (!empty($p['Inventory'])) {
                    continue;
                }

                foreach ($p['Inventory'] as $k => $v) {
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
        $p = $this->PlayerObject->getPlayer($fd);

        $msg = [
            'Grid'     => $param['res']['Grid'],
            'UniqueID' => $param['res']['UniqueID'],
            'To'       => $param['res']['To'],
            'Success'  => false,
        ];

        $res = $this->PlayerObject->getUserItemByID($p, $param['res']['Grid'], $param['res']['UniqueID']);

        if ($res[0] === false) {
            return ['EQUIP_ITEM', $msg];
        }

        $Inventory = $p['Inventory'];
        $Equipment = $p['Equipment'];
        $Storage   = $p['Storage'];

        switch ($param['res']['Grid']) {
            case $this->Enum::MirGridTypeInventory:
                $err            = $this->Bag->moveTo($Inventory, $res[0], $msg['To'], $Equipment);
                $p['Inventory'] = $Inventory;
                $p['Equipment'] = $Equipment;
                break;

            case $this->Enum::MirGridTypeStorage:
                $err            = $this->Bag->moveTo($Inventory, $res[0], $msg['To'], $Storage);
                $p['Inventory'] = $Inventory;
                $p['Storage']   = $Storage;
                break;
        }

        if (!$err) {
            return ['EQUIP_ITEM', $msg];
        }

        $msg['Success'] = true;
        $this->PlayerObject->refreshStats($p);
        $this->PlayerObject->setPlayer($p['fd'], $p);

        $this->PlayerObject->updateConcentration($p);
        $this->PlayerObject->broadcast($p, ['PLAYER_UPDATE', $this->MsgFactory->playerUpdate($p)]);

        return ['EQUIP_ITEM', $msg];
    }

    public function removeItem($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        $msg = [
            'Grid'     => $param['res']['Grid'],
            'UniqueID' => $param['res']['UniqueID'],
            'To'       => $param['res']['To'],
            'Success'  => false,
        ];

        $res = $this->PlayerObject->getUserItemByID($p, $this->Enum::MirGridTypeEquipment, $param['res']['UniqueID']);

        if ($res[0] === false) {
            return ['REMOVE_ITEM', $msg];
        }

        $Inventory = $p['Inventory'];
        $Equipment = $p['Equipment'];
        $Storage   = $p['Storage'];

        switch ($param['res']['Grid']) {
            case $this->Enum::MirGridTypeInventory:
                $this->Bag->moveTo($Equipment, $res[0], $msg['To'], $Inventory);
                $p['Equipment'] = $Equipment;
                $p['Inventory'] = $Inventory;
                break;

            case $this->Enum::MirGridTypeStorage:

                if (!$this->Util->stringEqualFold($p['CallingNPCPage'], $this->Enum::StorageKey)) {
                    return ['REMOVE_ITEM', $msg];
                }

                $this->Bag->moveTo($Equipment, $res[0], $msg['To'], $Storage);
                $p['Equipment'] = $Equipment;
                $p['Storage']   = $Storage;
                break;
        }

        $msg['Success'] = true;
        $this->PlayerObject->refreshStats($p);
        $this->PlayerObject->setPlayer($p['fd'], $p);

        $this->PlayerObject->updateConcentration($p);
        $this->PlayerObject->broadcast($p, ['PLAYER_UPDATE', $this->MsgFactory->playerUpdate($p)]);

        return ['REMOVE_ITEM', $msg];
    }

    public function moveItem($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        $msg = [
            'Grid'    => $param['res']['Grid'],
            'From'    => $param['res']['From'],
            'To'      => $param['res']['To'],
            'Success' => false,
        ];

        $Inventory = $p['Inventory'];
        $Storage   = $p['Storage'];
        $Trade     = $p['Trade'];

        switch ($param['res']['Grid']) {
            case $this->Enum::MirGridTypeInventory:
                $err            = $this->Bag->move($Inventory, $msg['From'], $msg['To']);
                $p['Inventory'] = $Inventory;
                break;

            case $this->Enum::MirGridTypeStorage:
                $err          = $this->Bag->move($Storage, $msg['From'], $msg['To']);
                $p['Storage'] = $Storage;
                break;

            case $this->Enum::MirGridTypeTrade:
                $err        = $this->Bag->moveTo($Trade, $msg['From'], $msg['To']);
                $p['Trade'] = $Trade;

                $this->PlayerObject->TradeItem();
                break;

            case $this->Enum::MirGridTypeRefine:
                // TODO
                break;
        }

        if (!$err) {
            $this->PlayerObject->receiveChat($p['fd'], '移动物品失败', $this->Enum::ChatTypeSystem);
        } else {
            $msg['Success'] = true;
            $this->PlayerObject->setPlayer($p['fd'], $p);
        }

        return ['MOVE_ITEM', $msg];
    }

    public function callNpc($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        $defaultNPC = $this->GameData->getDefaultNPC();

        if ($param['res']['ObjectID'] == $defaultNPC['ID']) {
            $npc = $defaultNPC;
        } else {
            $npc = $this->Map->getNpc($p['Map']['Info']['id'], $param['res']['ObjectID']);
        }

        if (!$npc) {
            EchoLog(sprintf('NPC不存在: %s %s', $param['res']['ObjectID'], $param['res']['Key']), 'w');
            return false;
        }

        $this->PlayerObject->callNPC1($p, $npc, $param['res']['Key']);
    }

    public function buyItem($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);
        if ($p['Dead']) {
            return false;
        }

        if (!$this->Util->stringEqualFold($p['CallingNPCPage'], [$this->Enum::BuySellKey, $this->Enum::BuyKey, $this->Enum::BuyBackKey, $this->Enum::BuyUsedKey, $this->Enum::PearlBuyKey])) {
            return false;
        }

        if (!$p['CallingNPC']) {
            return false;
        }

        $npc = $this->Map->getNpc($p['Map']['Info']['id'], $p['CallingNPC']);

        $this->Npc->buy($p, $npc, $param['res']['ItemIndex'], $param['res']['Count']);
    }

    public function dropItem($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);
        if ($p['Dead']) {
            return false;
        }

        $res = $this->PlayerObject->getUserItemByID($p, $this->Enum::MirGridTypeInventory, $param['res']['UniqueID']);
        if (!$res[1]) {
            return $this->MsgFactory->dropItem($param['res']['UniqueID'], $param['res']['Count'], false);
        }

        $item = $this->Item->newItem($p['Map'], $res[1]);

        $dropMsg = $this->Item->drop($item, $p['CurrentLocation'], 1);
        if (!$dropMsg) {
            $this->PlayerObject->receiveChat($p['fd'], sprintf('坐标(%s)附近没有合适的点放置物品;', $p['CurrentLocation']), $this->Enum::ChatTypeSystem);

            return $this->MsgFactory->dropItem($param['res']['UniqueID'], $param['res']['Count'], false);
        }

        if ($param['res']['Count'] >= $res[1]['count']) {
            $p['Inventory'] = $this->Bag->set($p['ID'], $p['Inventory'], $res[0], null);
        } else {
            $p['Inventory'] = $this->Bag->useCount($p['Inventory'], $res[0], $param['res']['Count']);
        }

        $this->PlayerObject->refreshBagWeight($p);

        $this->PlayerObject->setPlayer($fd, $p);

        return $this->MsgFactory->dropItem($param['res']['UniqueID'], $param['res']['Count'], true);
    }

    public function sellItem($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);
        if ($p['Dead'] || !$param['res']['Count']) {
            return $this->MsgFactory->sellItem($param['res']['UniqueID'], $param['res']['Count'], false);
        }

        if (!$this->Util->stringEqualFold($p['CallingNPCPage'], [$this->Enum::BuySellKey, $this->Enum::SellKey])) {
            return $this->MsgFactory->sellItem($param['res']['UniqueID'], $param['res']['Count'], false);
        }

        $index = -1;
        $temp  = null;

        foreach ($p['Inventory']['Items'] as $k => $v) {
            if (!isset($v['isset']) || !$v['isset'] || $param['res']['UniqueID'] != $v['id']) {
                continue;
            }

            $temp  = $v;
            $index = $k;
            break;
        }

        if (!$temp || $index == -1 || $param['res']['Count'] > $temp['count']) {
            return $this->MsgFactory->sellItem($param['res']['UniqueID'], $param['res']['Count'], false);
        }

        if ($this->Util->hasFlagUint16($temp['Info']['bind'], $this->Enum::BindModeDontSell)) {
            return $this->MsgFactory->sellItem($param['res']['UniqueID'], $param['res']['Count'], false);
        }

        $npc = $this->Map->getNpc($p['Map']['Info']['id'], $p['CallingNPC']);
        if (!$this->Npc->hasType($npc, $temp['Info']['type'])) {
            $this->PlayerObject->receiveChat($p['fd'], '不能在这里卖这类商品', $this->Enum::ChatTypeSystem);
            return $this->MsgFactory->sellItem($param['res']['UniqueID'], $param['res']['Count'], false);
        }

        if ($temp['Info']['stack_size'] > 1 && $param['res']['Count'] != $temp['count']) {
            $item          = $this->MsgFactory->newUserItem($temp['Info'], $this->Atomic->newObjectID());
            $item['count'] = $param['res']['Count'];

            if ($this->Item->price($item) + $p['Gold'] > 18446744073709551615) {
                return $this->MsgFactory->sellItem($param['res']['UniqueID'], $param['res']['Count'], false);
            }

            $temp['count'] -= $param['res']['Count'];
            $temp = $item;
        } else {
            $p['Inventory'] = $this->Bag->set($p['ID'], $p['Inventory'], $index, null);
        }

        $this->Npc->addBuyBack($npc, $p, $temp); //出售回购

        $this->PlayerObject->refreshBagWeight($p);

        $this->PlayerObject->gainGold($p, $this->Item->price($temp) / 2);

        $this->PlayerObject->setPlayer($fd, $p);

        return $this->MsgFactory->sellItem($param['res']['UniqueID'], $param['res']['Count'], true);
    }

    public function pickUp($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);
        if ($p['Dead']) {
            return false;
        }

        $items = [];

        $item = $this->Map->getObjectByPoint($p, $this->Enum::ObjectTypeItem);

        if (!$item) {
            return false;
        }

        if (empty($item['UserItem'])) {
            $this->PlayerObject->gainGold($p, $item['Gold']);
            $items[] = $item;
        } else {
            $item['UserItem']['Info']['count'] = $item['UserItem']['count'];
            if ($this->PlayerObject->gainItem($p, $item['UserItem']['Info'])) {
                $items[] = $item;
            }
        }

        $this->PlayerObject->setPlayer($fd, $p);

        foreach ($items as $key => $item) {
            $object = [
                'CurrentLocation' => $p['CurrentLocation'],
                'Map'             => $p['Map'],
                'ID'              => $item['ID'],
            ];

            $this->Map->deleteObject($object, $this->Enum::ObjectTypeItem);

            $this->Item->broadcast($object, $this->MsgFactory->objectRemove($item));
        }
    }

    public function changeAMode($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        $p['AMode'] = $param['res']['Mode'];

        $this->PlayerObject->setPlayer($fd, $p);

        $this->SendMsg->send($p['fd'], $this->MsgFactory->changeAMode($param['res']['Mode']));
    }

    public function changePMode($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);

        $p['PMode'] = $param['res']['Mode'];

        $this->PlayerObject->setPlayer($fd, $p);

        $this->SendMsg->send($p['fd'], $this->MsgFactory->changePMode($param['res']['Mode']));
    }

    public function useItem($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);
        if ($p['Dead']) {
            return false;
        }

        $res = $this->PlayerObject->getUserItemByID($p, $this->Enum::MirGridTypeInventory, $param['res']['UniqueID']);
        if (empty($res[1]) || $res[1]['id'] === 0 || !$this->PlayerObject->canUseItem($p, $res[1])) {
            return $this->MsgFactory->useItem($param['res']['UniqueID'], false);
        }

        $info = $res[1]['Info'];

        $msg = $this->MsgFactory->useItem($param['res']['UniqueID'], false);

        switch ($info['type']) {
            case $this->Enum::ItemTypePotion:
                $msg[1]['Success'] = $this->PlayerObject->userItemPotion($p, $res[1]);
                break;

            case $this->Enum::ItemTypeScroll:
                $msg[1]['Success'] = $this->PlayerObject->useItemScroll($p, $res[1]);
                break;

            case $this->Enum::ItemTypeBook:
                $msg[1]['Success'] = $this->PlayerObject->giveSkill($p, $res[1]);
                break;

            case $this->Enum::ItemTypeScript:
                $this->PlayerObject->callDefaultNPC($p, $this->Enum::DefaultNPCTypeUseItem, $info['shape']);
                $msg[1]['Success'] = true;
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

        if ($msg[1]['Success']) {
            if ($res[1]['count'] > 1) {
                $p['Inventory'] = $this->Bag->useCount($p['Inventory'], $res[0], 1);
            } else {
                $p['Inventory'] = $this->Bag->set($p['ID'], $p['Inventory'], $res[0], null);
            }

            $this->PlayerObject->refreshBagWeight($p);

            $this->PlayerObject->setPlayer($fd, $p);

            return $msg;
        }
    }

    public function dropGold($fd, $param)
    {
        $p = $this->PlayerObject->getPlayer($fd);
        if ($p['Dead']) {
            return false;
        }

        if ($p['Gold'] < $param['res']['Amount']) {
            return false;
        }

        $item = $this->Item->newGold($p['Map'], $param['res']['Amount']);

        $dropMsg = $this->Item->drop($item, $p['CurrentLocation'], 3);

        if (!$dropMsg) {
            $this->PlayerObject->receiveChat($p['fd'], sprintf('坐标(%s)附近没有合适的点放置物品;', $p['CurrentLocation']), $this->Enum::ChatTypeSystem);
            return false;
        }

        $this->PlayerObject->takeGold($p, $param['res']['Amount']);
    }
}
