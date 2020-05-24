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
            'width'           => $w,
            'height'          => $h,
            'version'         => $version,
            'info'            => [], //地图详情
            'safe_zone_infos' => [], //安全区
            'respawns'        => [], //怪物刷新
            'cells'           => [], //地图格子
            'doors_map'       => $this->Door->NewGrid($w, $h),
            'doors'           => [],
            'players'         => [],
            'monsters'        => [],
            'npcs'            => [],
            'actived_objects' => [],
            'actionList'      => [],
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
                $n                = $this->Npc->newNpc($npcInfo['map_id'], $this->Atomic->newObjectID(), $npcInfo);
                $n['info']        = $npcInfo;
                $n['object_type'] = $this->Enum::ObjectTypeNPC;

                if (!empty($n['id'])) {
                    $npc[$n['id']] = $n;
                }
            }
        }

        $respawns = [];
        $monsters = [];
        foreach ($respawnInfos as $respawnInfo) {
            if ($respawnInfo['map_id'] == $map['info']['id']) {
                $respawn    = $this->Respawn->newRespawn($map, $respawnInfo);
                $respawns[] = $respawn; //待生成信息
                $monster    = $this->Respawn->spawn($respawn, false); //生成信息
                if ($monster) {
                    $monsters = array_merge($monsters, $monster);
                }
            }
        }

        return ['npc' => $npc, 'monsters' => $monsters, 'respawns' => $respawns];
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
                $this->GameData->setMapmonster($object['map']['info']['id'], $object);
                break;

            case $this->Enum::ObjectTypeNPC:
                # code...
                break;
        }
    }

    public function getObjectByPoint($object, $type = null)
    {
        if (empty($object['id'])) {
            return false;
        }

        if ($type == null) {
            $type = !empty($object['object_type']) ? $object['object_type'] : null;
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

        if ($type == null) {
            $type = !empty($object['object_type']) ? $object['object_type'] : null;
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
        //给地图中人物同步登录玩家
        $players = $this->GameData->getMapPlayers($object['map']['info']['id']);
        if ($players) {
            foreach ($players as $k => $v) {
                $player = $this->PlayerObject->getIdPlayer($v['id']);
                if ($this->Point->inRange($currentPoint, $player['current_location'], $this->DataRange)) {
                    if ($player['id'] != $object['id']) {
                        $this->SendMsg->send($player['fd'], $msg);
                    }
                }
            }
        }
    }

    public function broadcastN($object)
    {
        //给登录玩家同步npc(未用暂留)
        $npcs = $this->GameData->getMapNpc($object['map']['info']['id']);
        if ($npcs) {
            foreach ($npcs as $k => $v) {
                if (!empty($v['current_location'])) {
                    if ($this->Point->inRange($object['current_location'], $v['current_location'], $this->DataRange)) {
                        $this->SendMsg->send($object['fd'], $this->MsgFactory->objectNPC($v));
                    }
                }
            }
        }
    }

    public function getCellPlayer($object, $depth)
    {
        $players    = $this->GameData->getMapPlayers($object['map']['info']['id']);
        $palyerData = [];
        if ($players) {
            foreach ($players as $k => $v) {
                $player = $this->PlayerObject->getIdPlayer($v['id']);
                if (!empty($player['current_location'])) {
                    if ($this->Point->inRange($object['current_location'], $player['current_location'], $depth)) {
                        $palyerData[] = $player;
                    }
                }
            }
        }

        return $palyerData;
    }

    public function getCellNpc($object, $depth)
    {
        $npcs = $this->GameData->getMapNpc($object['map']['info']['id']);

        $npcData = [];
        if ($npcs) {
            foreach ($npcs as $k => $v) {
                if (!empty($v['current_location'])) {
                    if ($this->Point->inRange($object['current_location'], $v['current_location'], $depth)) {
                        $npcData[] = $v;
                    }
                }
            }
        }

        return $npcData;
    }

    public function getCellItem($object, $depth)
    {
        $items = $this->GameData->getMapItem($object['map']['info']['id']);

        $itemData = [];
        if ($items) {
            foreach ($items as $k => $v) {
                if (!empty($v['current_location'])) {
                    if ($this->Point->inRange($object['current_location'], $v['current_location'], $depth)) {
                        $itemData[] = $v;
                    }
                }
            }
        }

        return $itemData;
    }

    public function getCellMonster($object, $depth)
    {
        $monsters    = $this->GameData->getMapMonster($object['map']['info']['id']);
        $monsterData = [];
        if ($monsters) {
            foreach ($monsters as $k => $v) {
                if (!empty($v['current_location'])) {
                    if ($this->Point->inRange($object['current_location'], $v['current_location'], $depth)) {
                        $monsterData[] = $v;
                    }
                }
            }
        }

        return $monsterData;
    }

    //给玩家同步地图中的对象
    public function rangeObject($p, $point, $depth, $fun)
    {
        //获取地图中邻近的人物
        $palyers = $this->getCellPlayer($p, $depth);
        if ($palyers) {
            foreach ($palyers as $k => $v) {
                if (!call_user_func_array($fun, [$p, $v])) {
                    return false;
                }
            }
        }

        //获取地图中邻近的npc
        $npcs = $this->getCellNpc($p, $depth);
        if ($npcs) {
            foreach ($npcs as $k => $v) {
                if (!call_user_func_array($fun, [$p, $v])) {
                    return false;
                }
            }
        }

        //获取地图中邻近的物品
        $items = $this->getCellItem($p, $depth);
        if ($items) {
            foreach ($items as $k => $v) {
                if (!call_user_func_array($fun, [$p, $v])) {
                    return false;
                }
            }
        }

        //获取地图中邻近的怪物
        $monsters = $this->getCellMonster($p, $depth);
        if ($monsters) {
            foreach ($monsters as $k => $v) {
                if (!call_user_func_array($fun, [$p, $v])) {
                    return false;
                }
            }
        }

        return true;
    }

    public function updateObject($object, $point, $type = null)
    {
        if ($type == null) {
            $type = !empty($object['object_type']) ? $object['object_type'] : null;
        }

        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                $this->PlayerObject->broadcast($object, $this->MsgFactory->objectPlayer($object));

                $mapInfo = $this->GameData->getMap($object['map']['info']['id']);
                $this->PlayerObject->enqueueAreaObjects($object, $this->getCell($mapInfo, $object['current_location']), $point);
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
