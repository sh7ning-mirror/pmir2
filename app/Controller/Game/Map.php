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
            'width'          => $w,
            'height'         => $h,
            'version'        => $version,
            'info'           => [], //地图详情
            'safe_zone_infos'  => [], //安全区
            'respawns'       => [], //怪物刷新
            'cells'          => [], //地图格子
            'doors_map'       => $this->Door->NewGrid($w, $h),
            'doors'          => [],
            'players'        => [],
            'monsters'       => [],
            'npcs'           => [],
            'actived_objects' => [],
            'actionList'     => [],
        ];
    }

    public function SetCell($m, $Point, $c)
    {
        $m = $this->SetCellXY($m, $Point['x'], $Point['y'], $c);
        return $m;
    }

    public function SetCellXY($m, $x, $y, $c)
    {
        $m['cells'][$x + $y * $m['width']] = $c;

        return $m;
    }

    public function AddDoor(&$m, $doorindex, $loc)
    {
        if (!empty($m['doors'])) {
            foreach ($m['doors'] as $d) {
                if ($d['index'] == $doorindex) {
                    return $d;
                }
            }
        } else {
            $m['doors'] = [];
        }

        $door                   = $this->Door->newDoor();
        $door['map']            = $m;
        $door['index']          = $doorindex;
        $door['location']       = $loc;
        $m['doors'][$doorindex] = $door;

        $m['doors_map'] = $this->Door->Set($m, $loc, $door);

    }

    public function initAll($map, $npcInfos, $respawnInfos, $safeZoneInfo)
    {
        $npc = [];
        foreach ($npcInfos as $npcInfo) {
            if ($npcInfo['map_id'] == $map['info']['id']) {
                $n         = $this->Npc->newNpc($npcInfo['map_id'], $this->Atomic->newObjectID(), $npcInfo);
                $n['info'] = $npcInfo;

                if (!empty($n['id'])) {
                    $npc[$n['id']] = $n;
                }
            }
        }

        // foreach ($respawnInfos as $respawnInfo) {
        //     if ($respawnInfo['map_id'] == $map['info']['id']) {

        //     }
        // }

        return ['npc' => $npc, 'monsters' => []];
    }

    public function addObject($object, $type)
    {
        if (empty($object['id'])) {
            return false;
        }

        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                $this->GameData->setMapPlayers($object['map']['info']['id'], $object);
                break;

            case $this->Enum::ObjectTypeItem:
                $this->GameData->setMapItem($object['map']['info']['id'], $object);
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
        if (empty($object['id'])) {
            return false;
        }

        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                $players = $this->GameData->getMapPlayers($object['map']['info']['id']);

                foreach ($players as $k => $v) {
                    if ($v['current_location']['x'] == $object['current_location']['x'] && $v['current_location']['y'] == $object['current_location']['y']) {
                        return $v;
                    }
                }

                break;

            case $this->Enum::ObjectTypeItem:
                return $this->GameData->getMapItem($object['map']['info']['id'], $object['current_location']);
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
        if (empty($object['id'])) {
            return false;
        }

        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                $this->GameData->delMapPlayers($object['map']['info']['id'], $object);
                break;

            case $this->Enum::ObjectTypeItem:
                $this->GameData->delMapItem($object['map']['info']['id'], $object['current_location']);
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
        return $this->getCellXY($m, $point['x'], $point['y']);
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
            'point'     => [
                'x' => $x,
                'y' => $y,
            ],
            'attribute' => $CellAttributeWalk,
            'objects'   => [],
        ];

        return $cell;
    }

    public function inMap($m, $x, $y)
    {
        return $x >= 0 && $x < $m['width'] && $y >= 0 && $y < $m['height'];
    }

    public function broadcastP($currentPoint, $msg, $object)
    {
        $players = $this->GameData->getMapPlayers($object['map']['info']['id']);

        foreach ($players as $k => $v) {
            $player = $this->PlayerObject->getIdPlayer($v['id']);
            if ($this->Point->inRange($currentPoint, $player['current_location'], $this->DataRange)) {
                if ($player['id'] != $object['id']) {
                    $this->SendMsg->send($player['fd'], $msg);
                }
            }
        }
    }

    public function broadcastN($object)
    {
        //同步npc
        $npcs = $this->GameData->getMapNpc($object['map']['info']['id']);
        if ($npcs) {
            foreach ($npcs as $k => $v) {
                if (!empty($v['current_location'])) {
                    // if ($this->Point->inRange($object['current_location'], $v['current_location'], $this->DataRange)) {
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

                $mapInfo = $this->GameData->getMap($object['map']['info']['id']);
                $this->PlayerObject->enqueueAreaObjects($object, $this->getCell($mapInfo, $object['current_location']), null);
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

        $door = $this->Door->get($mapInfo['doors_map'], $point);
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
        $px = $p['x'];
        $py = $p['y'];

        for ($d = 0; $d <= $depth; $d++) {
            for ($y = $py - $d; $y <= $py + $d; $y++) {
                if ($y < 0) {
                    continue;
                }

                if ($y >= $m['height']) {
                    break;
                }

                for ($x = $px - $d; $x <= $px + $d;) {

                    if ($x >= $m['width']) {
                        break;
                    }

                    if ($x >= 0) {
                        if (!call_user_func_array($fun, [$object, $m['info']['id'], $x, $y])) {
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
