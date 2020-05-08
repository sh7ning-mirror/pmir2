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

        $p['CurrentLocation']  = $point;
        $p['CurrentDirection'] = $param['res']['Direction'];

        if ($objectPlayer->checkMovement($point, $p)) {
            $objectPlayer->setPlayer($fd, $p);
            return false;
        }

        if (!getObject('Map')->updateObject($p, $point, getObject('Enum')::ObjectTypePlayer)) {
            $objectPlayer->setPlayer($fd, $p);
            return ['USER_LOCATION', $data];
        }

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
            $objectPlayer->setPlayer($fd, $p);
            return ['USER_LOCATION', $data];
        }

        $p['CurrentLocation']  = $point;
        $p['CurrentDirection'] = $param['res']['Direction'];

        $SendMsg->send($fd, ['USER_LOCATION', $data]);

        $objectPlayer->broadcast($p, ['OBJECT_RUN', getObject('MsgFactory')->objectRun($p)]);

        $objectPlayer->setPlayer($fd, $p);
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

        $objectPlayer->broadcast($p, ['OBJECT_TURN', getObject('MsgFactory')->objectTurn($p)]);

        $objectPlayer->setPlayer($fd, $p);
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
        
        co(function () use ($fd, $p, $Enum,$objectPlayer) {

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
        $p = getObject('PlayerObject')->getPlayer($fd);

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

        getObject('PlayerObject')->broadcast($p, ['OBJECT_CHAT', $msg]);

        return ['OBJECT_CHAT', $msg];
    }

    public function openDoor($fd, $param)
    {
        $objectPlayer = getObject('PlayerObject');

        $p = $objectPlayer->getPlayer($fd);

        $objectPlayer->openDoor($p, $param['res']['DoorIndex']);
    }
}
