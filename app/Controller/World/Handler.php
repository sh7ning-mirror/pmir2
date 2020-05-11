<?php
declare (strict_types = 1);

namespace App\Controller\World;

/**
 *
 */
class Handler
{
    public function walk($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');

        $p = $objectPlayer->getPlayer($fd);

        $point = getObject('Point')->NextPoint($p['CurrentLocation'], $param['res']['Direction'], 1);

        $data = [
            'Location'  => $point,
            'Direction' => $param['res']['Direction'],
        ];

        if ($objectPlayer->checkMovement($point, $p)) {
            return false;
        }

        if (!getObject('Map')->updateObject($p, $point, getObject('Enum')::ObjectTypePlayer)) {
            $p['CurrentLocation']  = $point;
            $p['CurrentDirection'] = $param['res']['Direction'];
            $objectPlayer->setPlayer($fd, $p);
            return ['USER_LOCATION', $data];
        }

        $p['CurrentLocation']  = $point;
        $p['CurrentDirection'] = $param['res']['Direction'];
        $objectPlayer->setPlayer($fd, $p);

        $objectPlayer->broadcast($p, ['OBJECT_WALK', getObject('MsgFactory')->objectWalk($p)]);

        return ['USER_LOCATION', $data];
    }

    public function run($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');
        $Map          = getObject('Map');
        $SendMsg      = getObject('SendMsg');
        $objectPoint  = getObject('Point');

        $steps = 2;

        $p = $objectPlayer->getPlayer($fd);

        for ($i = 1; $i <= $steps; $i++) {

            $point = $objectPoint->NextPoint($p['CurrentLocation'], $param['res']['Direction'], $i);
            $data  = [
                'Location'  => $point,
                'Direction' => $param['res']['Direction'],
            ];

            if (!$Map->checkDoorOpen($p['Map']['Info']['id'], $point)) {
                $objectPlayer->setPlayer($fd, $p);
                $SendMsg->send($fd, ['USER_LOCATION', $data]);
                return false;
            }

            if ($objectPlayer->checkMovement($point, $p)) {
                $objectPlayer->setPlayer($fd, $p);
                return false;
            }
        }

        if (!$Map->updateObject($p, $point, getObject('Enum')::ObjectTypePlayer)) {
            $p['CurrentLocation']  = $point;
            $p['CurrentDirection'] = $param['res']['Direction'];
            $objectPlayer->setPlayer($fd, $p);
            return ['USER_LOCATION', $data];
        }

        $p['CurrentLocation']  = $point;
        $p['CurrentDirection'] = $param['res']['Direction'];
        $objectPlayer->setPlayer($fd, $p);

        $SendMsg->send($fd, ['USER_LOCATION', $data]);

        $objectPlayer->broadcast($p, ['OBJECT_RUN', getObject('MsgFactory')->objectRun($p)]);
    }

    public function turn($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');
        $p            = $objectPlayer->getPlayer($fd);

        $data = [
            'Location'  => $p['CurrentLocation'],
            'Direction' => $param['res']['Direction'],
        ];

        $p['CurrentDirection'] = $param['res']['Direction'];

        getObject('SendMsg')->send($fd, ['USER_LOCATION', $data]);

        $objectPlayer->setPlayer($fd, $p);

        $objectPlayer->broadcast($p, ['OBJECT_TURN', getObject('MsgFactory')->objectTurn($p)]);
    }

    public function logOut($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');
        $p            = $objectPlayer->getPlayer($fd);
        $Enum         = getObject('Enum');

        if (!$p || !isset($p['GameStage']) || $p['GameStage'] != $Enum::GAME) {
            return false;
        }

        $Characters = getObject('Character')->getAccountCharacters($p['account']);

        $objectPlayer->stopGame($p);

        co(function () use ($fd, $p, $Enum, $objectPlayer) {

            $p['GameStage'] = $Enum::SELECT;

            $objectPlayer->setPlayer($fd, $p);

            $objectPL = getObject('PlayersList');

            //保存玩家属性
            $objectPL->saveData($fd, $p);

            //删除玩家
            $objectPL->delPlayersList($fd, $p);
        });

        return ['LOG_OUT_SUCCESS', ['Count' => count($Characters), 'Characters' => $Characters]];
    }

    public function chat($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');
        $p            = $objectPlayer->getPlayer($fd);

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

        $msg = getObject('MsgFactory')->objectChat($p, $param['res']['Message'], getObject('Enum')::ChatTypeNormal);

        $objectPlayer->broadcast($p, ['OBJECT_CHAT', $msg]);

        return ['OBJECT_CHAT', $msg];
    }

    public function openDoor($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');

        $p = $objectPlayer->getPlayer($fd);

        $objectPlayer->openDoor($p, $param['res']['DoorIndex']);
    }

    public function refineCancel($fd, $param)
    {
        # code...
    }

    public function equipItem($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');
        $Enum         = getObject('Enum');
        $Bag          = getObject('Bag');
        $p            = $objectPlayer->getPlayer($fd);

        $msg = [
            'Grid'     => $param['res']['Grid'],
            'UniqueID' => $param['res']['UniqueID'],
            'To'       => $param['res']['To'],
            'Success'  => false,
        ];

        $index = $objectPlayer->getUserItemByID($p, $param['res']['Grid'], $param['res']['UniqueID']);

        if ($index === false) {
            return ['EQUIP_ITEM', $msg];
        }

        $Inventory = $p['Inventory'];
        $Equipment = $p['Equipment'];
        $Storage   = $p['Storage'];

        switch ($param['res']['Grid']) {
            case $Enum::MirGridTypeInventory:
                $err            = $Bag->moveTo($Inventory, $index, $msg['To'], $Equipment);
                $p['Inventory'] = $Inventory;
                $p['Equipment'] = $Equipment;
                break;

            case $Enum::MirGridTypeStorage:
                $err            = $Bag->moveTo($Inventory, $index, $msg['To'], $Storage);
                $p['Inventory'] = $Inventory;
                $p['Storage']   = $Storage;
                break;
        }

        if (!$err) {
            return ['EQUIP_ITEM', $msg];
        }

        $msg['Success'] = true;
        $objectPlayer->refreshStats($p);
        $objectPlayer->setPlayer($p['fd'], $p);

        $objectPlayer->updateConcentration($p);
        $objectPlayer->broadcast($p, ['PLAYER_UPDATE', getObject('MsgFactory')->playerUpdate($p)]);

        return ['EQUIP_ITEM', $msg];
    }

    public function removeItem($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');
        $Enum         = getObject('Enum');
        $Bag          = getObject('Bag');
        $p            = $objectPlayer->getPlayer($fd);

        $msg = [
            'Grid'     => $param['res']['Grid'],
            'UniqueID' => $param['res']['UniqueID'],
            'To'       => $param['res']['To'],
            'Success'  => false,
        ];

        $index = $objectPlayer->getUserItemByID($p, $Enum::MirGridTypeEquipment, $param['res']['UniqueID']);

        if ($index === false) {
            return ['REMOVE_ITEM', $msg];
        }

        $Inventory = $p['Inventory'];
        $Equipment = $p['Equipment'];
        $Storage   = $p['Storage'];

        switch ($param['res']['Grid']) {
            case $Enum::MirGridTypeInventory:
                $Bag->moveTo($Equipment, $index, $msg['To'], $Inventory);
                $p['Equipment'] = $Equipment;
                $p['Inventory'] = $Inventory;
                break;

            case $Enum::MirGridTypeStorage:

                if (!getObject('Util')->stringEqualFold($p['CallingNPCPage'], $Enum::StorageKey)) {
                    return ['REMOVE_ITEM', $msg];
                }

                $Bag->moveTo($Equipment, $index, $msg['To'], $Storage);
                $p['Equipment'] = $Equipment;
                $p['Storage']   = $Storage;
                break;
        }

        $msg['Success'] = true;
        $objectPlayer->refreshStats($p);
        $objectPlayer->setPlayer($p['fd'], $p);

        $objectPlayer->updateConcentration($p);
        $objectPlayer->broadcast($p, ['PLAYER_UPDATE', getObject('MsgFactory')->playerUpdate($p)]);

        return ['REMOVE_ITEM', $msg];
    }

    public function moveItem($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');
        $Enum         = getObject('Enum');
        $Bag          = getObject('Bag');
        $p            = $objectPlayer->getPlayer($fd);

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
            case $Enum::MirGridTypeInventory:
                $err            = $Bag->move($Inventory, $msg['From'], $msg['To']);
                $p['Inventory'] = $Inventory;
                break;

            case $Enum::MirGridTypeStorage:
                $err          = $Bag->move($Storage, $msg['From'], $msg['To']);
                $p['Storage'] = $Storage;
                break;

            case $Enum::MirGridTypeTrade:
                $err        = $Bag->moveTo($Trade, $msg['From'], $msg['To']);
                $p['Trade'] = $Trade;

                $objectPlayer->TradeItem();
                break;

            case $Enum::MirGridTypeRefine:
                // TODO
                break;
        }

        if (!$err) {
            $objectPlayer->receiveChat('移动物品失败', $Enum::ChatTypeSystem);
        } else {
            $msg['Success'] = true;
            $objectPlayer->setPlayer($p['fd'], $p);
        }

        return ['MOVE_ITEM', $msg];
    }

}
