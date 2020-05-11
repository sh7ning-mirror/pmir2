<?php
namespace App\Controller\Game;

/**
 *
 */
class Map
{
    public $DataRange = 20;

    public function NewMap($w, $h, $version)
    {
        return [
            'Width'          => $w,
            'Height'         => $h,
            'Version'        => $version,
            'Info'           => [], //地图详情
            'SafeZoneInfos'  => [], //安全区
            'Respawns'       => [], //怪物刷新
            'cells'          => [], //地图格子
            'doorsMap'       => getObject('Door')->NewGrid($w, $h),
            'doors'          => [],
            'players'        => [],
            'monsters'       => [],
            'npcs'           => [],
            'activedObjects' => [],
            'ActionList'     => [],
        ];
    }

    public function SetCell($m, $Point, $c)
    {
        $m = $this->SetCellXY($m, $Point['X'], $Point['Y'], $c);
        return $m;
    }

    public function SetCellXY($m, $x, $y, $c)
    {
        $m['cells'][$x + $y * $m['Width']] = $c;

        return $m;
    }

    public function AddDoor(&$m, $doorindex, $loc)
    {
        if (!empty($m['doors'])) {
            foreach ($m['doors'] as $d) {
                if ($d['Index'] == $doorindex) {
                    return $d;
                }
            }
        } else {
            $m['doors'] = [];
        }

        $door                   = getObject('Door')->newDoor();
        $door['Map']            = $m;
        $door['Index']          = $doorindex;
        $door['Location']       = $loc;
        $m['doors'][$doorindex] = $door;

        $m['doorsMap'] = getObject('Door')->Set($m, $loc, $door);

    }

    public function initAll($map, $npcInfos, $respawnInfos, $safeZoneInfo)
    {
        $Atomic = getObject('Atomic');
        $Npc    = getObject('Npc');

        $npc = [];
        foreach ($npcInfos as $npcInfo) {
            if ($npcInfo['map_id'] == $map['Info']['id']) {
                $n         = $Npc->newNpc($npcInfo['map_id'], $Atomic->newObjectID(), $npcInfo);
                $n['Info'] = $npcInfo;
                $npc[]     = $n;
            }
        }

        // foreach ($respawnInfos as $respawnInfo) {
        //     if ($respawnInfo['map_id'] == $map['Info']['id']) {

        //     }
        // }

        return ['npc' => $npc, 'monsters' => []];
    }

    public function addObject($p)
    {
        if (empty($p['ID'])) {
            return false;
        }

        getObject('GameData')->setMapPlayers($p['Map']['Info']['id'], $p);
    }

    public function deleteObject($p)
    {
        getObject('GameData')->delMapPlayers($p['Map']['Info']['id'], $p);
    }

    public function getCell($m, $point)
    {
        return $this->getCellXY($m, $point['X'], $point['Y']);
    }

    public function getCellXY($m, $x, $y)
    {
        if ($this->inMap($m, $x, $y)) {
            return $this->getCells($m, $x, $y);
        } else {
            return false;
        }
    }

    public function getCells($m, $x, $y)
    {
        $CellAttributeWalk = getObject('Enum')::CellAttributeWalk;

        $cell = [
            'Point'     => [
                'X' => $x,
                'Y' => $y,
            ],
            'Attribute' => $CellAttributeWalk,
            'objects'   => [],
        ];

        return $cell;
    }

    public function inMap($m, $x, $y)
    {
        return $x >= 0 && $x < $m['Width'] && $y >= 0 && $y < $m['Height'];
    }

    public function broadcastP($currentPoint, $msg, $object)
    {
        $GameData = getObject('GameData');
        $objectPl = getObject('PlayerObject');
        $Point    = getObject('Point');
        $SendMsg  = getObject('SendMsg');

        $players = $GameData->getMapPlayers($object['Map']['Info']['id']);

        foreach ($players as $k => $v) {
            $player = $objectPl->getIdPlayer($v['ID']);
            if ($Point->inRange($currentPoint, $player['CurrentLocation'], $this->DataRange)) {
                if ($player['ID'] != $object['ID']) {
                    $SendMsg->send($player['fd'], $msg);
                }
            }
        }
    }

    public function broadcastN($object)
    {
        $GameData   = getObject('GameData');
        $Point      = getObject('Point');
        $SendMsg    = getObject('SendMsg');
        $MsgFactory = getObject('MsgFactory');

        //同步npc
        $npcs = $GameData->getMapNpc($object['Map']['Info']['id']);
        if ($npcs) {
            foreach ($npcs as $k => $v) {
                if (!empty($v['CurrentLocation'])) {
                    // if ($Point->inRange($object['CurrentLocation'], $v['CurrentLocation'], $this->DataRange)) {
                        $SendMsg->send($object['fd'], ['OBJECT_NPC', $MsgFactory->objectNPC($v)]);
                    // }
                }
            }
        }
    }

    public function rangeObject($object)
    {
        $objectPl   = getObject('PlayerObject');
        $MsgFactory = getObject('MsgFactory');

        $objectPl->broadcast($object, ['OBJECT_PLAYER', $MsgFactory->objectPlayer($object)]);

        $this->broadcastN($object);
    }

    public function updateObject($object, $point, $type)
    {
        $Enum         = getObject('Enum');
        $objectPlayer = getObject('PlayerObject');
        $MsgFactory   = getObject('MsgFactory');
        $Monster      = getObject('Monster');

        switch ($type) {
            case $Enum::ObjectTypePlayer:
                $objectPlayer->broadcast($object, ['OBJECT_PLAYER', $MsgFactory->objectPlayer($object)]);

                $mapInfo = getObject('GameData')->getMap($object['Map']['Info']['id']);
                $objectPlayer->enqueueAreaObjects($object, $this->getCell($mapInfo, $object['CurrentLocation']), null);
                break;

            case $Enum::ObjectTypeMonster:
                $Monster->broadcast($object, $MsgFactory->objectMonster($object));
                break;
        }

        return true;
    }

    //检查是否开门
    public function checkDoorOpen($map_id, $point)
    {
        $mapInfo = getObject('GameData')->getMap($map_id);

        $objectDoor = getObject('Door');

        $door = $objectDoor->get($mapInfo['doorsMap'], $point);
        if (!$door) {
            return true;
        }

        return $objectDoor->isOpen($door);
    }

    public function openDoor($map_id, $doorindex)
    {
        $mapInfo    = getObject('GameData')->getMap($map_id);
        $objectDoor = getObject('Door');

        $door = $mapInfo['doors'][$doorindex] ?: null;

        if (!$door) {
            EchoLog(sprintf('此区域没有门: %s', $doorindex), 'e');
            return false;
        }

        $objectDoor->setOpen($map_id, $doorindex, true);

        return true;
    }
}
