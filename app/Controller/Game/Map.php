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
        $npc        = [];
        $cellObject = [];
        foreach ($npcInfos as $npcInfo) {
            if ($npcInfo['map_id'] == $map['info']['id']) {
                $n                = $this->Npc->newNpc($map, $this->Atomic->newObjectID(), $npcInfo);
                $n['info']        = $npcInfo;
                $n['object_type'] = $this->Enum::ObjectTypeNPC;

                if (!empty($n['id']) && !empty($map['width'])) {
                    $npc[$n['id']] = $n;
                    $cellId        = $this->Cell->getCellId($npcInfo['location_x'], $npcInfo['location_y'], $map['width'], $map['height']);

                    $cellObject[$cellId][] = $n;
                }
            }
        }

        $respawns = [];
        $monsters = [];
        foreach ($respawnInfos as $respawnInfo) {
            if ($respawnInfo['map_id'] == $map['info']['id']) {
                $respawn    = $this->Respawn->newRespawn($map, $respawnInfo);
                $monster    = $this->Respawn->spawn($respawn, false); //生成信息
                $respawns[] = $respawn; //待生成信息
                if ($monster) {
                    $monsters = array_merge($monsters, $monster);

                    foreach ($monster as $k => $v) {
                        $cellId = $this->Cell->getCellId($v['current_location']['x'], $v['current_location']['y'], $v['map']['width'], $v['map']['height']);
                        $cellObject[$cellId][] = $v;
                    }
                }
            }
        }

        return ['npc' => $npc, 'monsters' => $monsters, 'respawns' => $respawns, 'cellObject' => $cellObject];
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

    public function deleteObject($object, $type=null)
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
                if($player['id'] != $object['id'])
                {
                    if ($this->Point->inRange($currentPoint, $player['current_location'], $this->DataRange)) {
                        if ($player['id'] !== $object['id']) {
                            $this->SendMsg->send($player['fd'], $msg);
                        }
                    }
                }
            }
        }
    }

    public function broadcastN($object)
    {
        //给登录玩家同步npc(未用暂留)
        $npcs = $this->GameData->getMapNpcIds($object['map']['info']['id']);
        if ($npcs) {
            foreach ($npcs as $k => $v) {
                $info = $this->GameData->getMapNpcInfo($object['map']['info']['id'], $v);
                if (!empty($info['current_location'])) {
                    if ($this->Point->inRange($object['current_location'], $info['current_location'], $this->DataRange)) {
                        $this->SendMsg->send($object['fd'], $this->MsgFactory->objectNPC($info));
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
        $npcs = $this->GameData->getMapNpcIds($object['map']['info']['id']);

        $npcData = [];
        if ($npcs) {
            foreach ($npcs as $k => $v) {
                $info = $this->GameData->getMapNpcInfo($object['map']['info']['id'], $v);
                if (!empty($info['current_location'])) {
                    if ($this->Point->inRange($object['current_location'], $info['current_location'], $depth)) {
                        $npcData[] = $info;
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
                $this->PlayerObject->enqueueAreaObjects($object, $object['current_location'], $point, $mapInfo);
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
        $door = $this->Door->get($map_id, $point);
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

    public function rangeCell($object, $m, $p, $depth, $fun)
    {
        list($px, $py) = [$p['x'], $p['y']];

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

    public function calcDiff($map, $from, $to, $datarange = 20)
    {
        list($fx, $fy, $tx, $ty) = [$from['x'], $from['y'], $to['x'], $to['y']];
        list($xChange, $yChange) = [$tx - $fx, $ty - $fy];

        $set = [];

        if ($xChange > 0) {
            // 右移
            for ($x = 0; $x < $xChange; $x++) {
                for ($y = $fy - $datarange; $y <= $fy + $datarange; $y++) {
                    $cellId       = $this->Cell->getCellId($fx - $datarange + $x, $y, $map['width'], $map['height']);
                    $set[$cellId] = false; // 左
                }

                for ($y = $ty - $datarange; $y <= $ty + $datarange; $y++) {
                    $cellId       = $this->Cell->getCellId($tx + $datarange - $x, $y, $map['width'], $map['height']);
                    $set[$cellId] = true; // 右
                }
            }
        } else {
            // 左移
            for ($x = 0; $x > $xChange; $x--) {
                for ($y = $ty - $datarange; $y <= $ty + $datarange; $y++) {
                    $cellId       = $this->Cell->getCellId($tx - $datarange - $x, $y, $map['width'], $map['height']);
                    $set[$cellId] = true; // 左
                }

                for ($y = $fy - $datarange; $y <= $fy + $datarange; $y++) {
                    $cellId       = $this->Cell->getCellId($fx + $datarange + $x, $y, $map['width'], $map['height']);
                    $set[$cellId] = false; // 右
                }
            }
        }

        if ($yChange < 0) {
            // 上移
            for ($y = 0; $y > $yChange; $y--) {
                for ($x = $tx - $datarange; $x <= $tx + $datarange; $x++) {
                    $cellId       = $this->Cell->getCellId($x, $ty - $datarange - $y, $map['width'], $map['height']);
                    $set[$cellId] = true; // 上
                }
                for ($x = $fx - $datarange; $x <= $fx + $datarange; $x++) {
                    $cellId       = $this->Cell->getCellId($x, $fy + $datarange + $y, $map['width'], $map['height']);
                    $set[$cellId] = false; // 下
                }
            }
        } else {
            // 下移
            for ($y = 0; $y < $yChange; $y++) {
                for ($x = $fx - $datarange; $x <= $fx + $datarange; $x++) {
                    $cellId       = $this->Cell->getCellId($x, $fy - $datarange + $y, $map['width'], $map['height']);
                    $set[$cellId] = false; // 上
                }
                for ($x = $tx - $datarange; $x <= $tx + $datarange; $x++) {
                    $cellId       = $this->Cell->getCellId($x, $ty + $datarange - $y, $map['width'], $map['height']);
                    $set[$cellId] = true; // 下
                }
            }
        }

        return $set;
    }
}
