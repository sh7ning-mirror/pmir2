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
            'Info'           => [], //地图想去
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

        // return $m;
    }

    public function InitAll()
    {
        # code...
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

    public function broadcastP($currentPoint, $msg, $me)
    {
        $GameData = getObject('GameData');
        $objectPl = getObject('PlayerObject');

        $players = $GameData->getMapPlayers($me['Map']['Info']['id']);

        $Point   = getObject('Point');
        $SendMsg = getObject('SendMsg');

        foreach ($players as $k => $v) {
            $player = $objectPl->getPlayer($v['fd']);
            if ($Point->inRange($currentPoint, $player['CurrentLocation'], $this->DataRange)) {
                if ($player['ID'] != $me['ID']) {
                    $SendMsg->send($player['fd'], $msg);
                }
            }
        }
    }

    public function rangeObject()
    {
        # code...
    }

    public function updateObject($object, $point, $type)
    {
        $Enum         = getObject('Enum');
        $objectPlayer = getObject('PlayerObject');
        $MsgFactory   = getObject('MsgFactory');
        $Monster      = getObject('Monster');

        switch ($type) {
            case $Enum::ObjectTypePlayer:
                $objectPlayer->broadcast($object, ['OBJECT_PLAYER',$MsgFactory->objectPlayer($object)]);
                $objectPlayer->enqueueAreaObjects($object, $this->getCell($object['Map'], $object['CurrentLocation']), null);
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
