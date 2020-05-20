<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Map extends AbstractController
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
            'doorsMap'       => $this->Door->NewGrid($w, $h),
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

        $door                   = $this->Door->newDoor();
        $door['Map']            = $m;
        $door['Index']          = $doorindex;
        $door['Location']       = $loc;
        $m['doors'][$doorindex] = $door;

        $m['doorsMap'] = $this->Door->Set($m, $loc, $door);

    }

    public function initAll($map, $npcInfos, $respawnInfos, $safeZoneInfo)
    {
        $npc = [];
        foreach ($npcInfos as $npcInfo) {
            if ($npcInfo['map_id'] == $map['Info']['id']) {
                $n         = $this->Npc->newNpc($npcInfo['map_id'], $this->Atomic->newObjectID(), $npcInfo);
                $n['Info'] = $npcInfo;

                if (!empty($n['ID'])) {
                    $npc[$n['ID']] = $n;
                }
            }
        }

        // foreach ($respawnInfos as $respawnInfo) {
        //     if ($respawnInfo['map_id'] == $map['Info']['id']) {

        //     }
        // }

        return ['npc' => $npc, 'monsters' => []];
    }

    public function addObject($object, $type)
    {
        if (empty($object['ID'])) {
            return false;
        }

        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                $this->GameData->setMapPlayers($object['Map']['Info']['id'], $object);
                break;

            case $this->Enum::ObjectTypeItem:
                $this->GameData->setMapItem($object['Map']['Info']['id'], $object);
                break;

            case $this->Enum::ObjectTypeMonster:
                # code...
                break;

            case $this->Enum::ObjectTypeNPC:
                # code...
                break;
        }
    }

    public function getObjectByPoint($object, $type)
    {
        if (empty($object['ID'])) {
            return false;
        }

        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                $players = $this->GameData->getMapPlayers($object['Map']['Info']['id']);

                foreach ($players as $k => $v) {
                    if ($v['CurrentLocation']['X'] == $object['CurrentLocation']['X'] && $v['CurrentLocation']['Y'] == $object['CurrentLocation']['Y']) {
                        return $v;
                    }
                }

                break;

            case $this->Enum::ObjectTypeItem:
                return $this->GameData->getMapItem($object['Map']['Info']['id'], $object['CurrentLocation']);
                break;

            case $this->Enum::ObjectTypeMonster:
                # code...
                break;

            case $this->Enum::ObjectTypeNPC:
                # code...
                break;
        }
    }

    public function deleteObject($object, $type)
    {
        if (empty($object['ID'])) {
            return false;
        }

        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                $this->GameData->delMapPlayers($object['Map']['Info']['id'], $object);
                break;

            case $this->Enum::ObjectTypeItem:
                $this->GameData->delMapItem($object['Map']['Info']['id'], $object['CurrentLocation']);
                break;

            case $this->Enum::ObjectTypeMonster:
                # code...
                break;

            case $this->Enum::ObjectTypeNPC:
                # code...
                break;
        }
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
        $CellAttributeWalk = $this->Enum::CellAttributeWalk;

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
        $players = $this->GameData->getMapPlayers($object['Map']['Info']['id']);

        foreach ($players as $k => $v) {
            $player = $this->PlayerObject->getIdPlayer($v['ID']);
            if ($this->Point->inRange($currentPoint, $player['CurrentLocation'], $this->DataRange)) {
                if ($player['ID'] != $object['ID']) {
                    $this->SendMsg->send($player['fd'], $msg);
                }
            }
        }
    }

    public function broadcastN($object)
    {
        //同步npc
        $npcs = $this->GameData->getMapNpc($object['Map']['Info']['id']);
        if ($npcs) {
            foreach ($npcs as $k => $v) {
                if (!empty($v['CurrentLocation'])) {
                    // if ($this->Point->inRange($object['CurrentLocation'], $v['CurrentLocation'], $this->DataRange)) {
                    $this->SendMsg->send($object['fd'], ['OBJECT_NPC', $this->MsgFactory->objectNPC($v)]);
                    // }
                }
            }
        }
    }

    public function rangeObject($object)
    {
        $this->PlayerObject->broadcast($object, ['OBJECT_PLAYER', $this->MsgFactory->objectPlayer($object)]);

        $this->broadcastN($object);
    }

    public function updateObject($object, $point, $type)
    {
        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                $this->PlayerObject->broadcast($object, ['OBJECT_PLAYER', $this->MsgFactory->objectPlayer($object)]);

                $mapInfo = $this->GameData->getMap($object['Map']['Info']['id']);
                $this->PlayerObject->enqueueAreaObjects($object, $this->getCell($mapInfo, $object['CurrentLocation']), null);
                break;

            case $this->Enum::ObjectTypeMonster:
                $this->Monster->broadcast($object, $this->MsgFactory->objectMonster($object));
                break;
        }

        return true;
    }

    //检查是否开门
    public function checkDoorOpen($map_id, $point)
    {
        $mapInfo = $this->GameData->getMap($map_id);

        $door = $this->Door->get($mapInfo['doorsMap'], $point);
        if (!$door) {
            return true;
        }

        return $this->Door->isOpen($door);
    }

    public function openDoor($map_id, $doorindex)
    {
        $mapInfo = $this->GameData->getMap($map_id);

        $door = $mapInfo['doors'][$doorindex] ?: null;

        if (!$door) {
            EchoLog(sprintf('此区域没有门: %s', $doorindex), 'e');
            return false;
        }

        $this->Door->setOpen($map_id, $doorindex, true);

        return true;
    }

    public function getNpc($map_id, $id)
    {
        $key = 'map:npcs_' . $map_id;

        $npcs = json_decode($this->Redis->get($key), true);

        if (!$npcs) {
            return false;
        }

        return $npcs[$id] ?? null;
    }

    public function rangeCell($object, $m, $p, $depth, $fun)
    {
        $px = $p['X'];
        $py = $p['Y'];

        for ($d = 0; $d <= $depth; $d++) {
            for ($y = $py - $d; $y <= $py + $d; $y++) {
                if ($y < 0) {
                    continue;
                }

                if ($y >= $m['Height']) {
                    break;
                }

                for ($x = $px - $d; $x <= $px + $d;) {

                    if ($x >= $m['Width']) {
                        break;
                    }

                    if ($x >= 0) {
                        if (!call_user_func_array($fun, [$object, $m['Info']['id'], $x, $y])) {
                            return true;
                        }
                    }

                    if ($y - $py == $d || $y - $py == -$d) {
                        $x++; // x += 1
                    } else {
                        $x += $d * 2;
                    }
                }
            }
        }
    }
}
