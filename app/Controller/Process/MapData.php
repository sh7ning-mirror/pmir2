<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class MapData extends AbstractController
{
    public static $mapIdInfos;
    public static $maps;
    public static $mapsFileName;
    public static $mapPlayers;
    public static $mapNpcs;
    public static $mapMonsters;
    public static $mapRespawns;
    public static $mapItem;
    public static $doorsMap;

    public function __construct()
    {

    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('map', $where);

        $path = config('settings_path');

        $cells = [];

        if ($res['list']) {
            $uppercaseNameRealNameMap = [];
            $file                     = new \FilesystemIterator($path . '/Maps/');

            foreach ($file as $fileinfo) {
                $filename                                        = $fileinfo->getFilename();
                $filepath                                        = $path . '/Maps/' . $filename;
                $uppercaseNameRealNameMap[strtoupper($filename)] = $filepath;
            }

            $stime = microtime(true);

            $num = 0;

            foreach ($res['list'] as $k => $v) {

                $mapIDInfoMap[$v['id']] = $v;

                //加载地图数据
                $m              = $this->MapLoader->loadMap($uppercaseNameRealNameMap[strtoupper(trim($v['file_name']) . ".map")]);
                $v['file_name'] = strtoupper(trim($v['file_name']));
                $m['id']        = $v['id'];
                $m['info']      = $v;

                self::$doorsMap[$v['id']] = !empty($m['doors_map']) ? $m['doors_map'] : [];

                if (isset($m['doors_map'])) {
                    unset($m['doors_map']);
                }

                self::$maps[$v['id']] = $m;

                self::$mapsFileName[$v['file_name']] = $m;

                $map_data = $this->Map->initAll($m, $this->NpcData::$npcInfos, $this->RespawnData::$respawnInfos, $this->SafeZoneData::$safeZoneInfos);

                //生成地图缓存
                self::$mapPlayers[$v['id']]  = [];
                self::$mapNpcs[$v['id']]     = $map_data['npc'];
                self::$mapMonsters[$v['id']] = $map_data['monsters'];
                self::$mapRespawns[$v['id']] = $map_data['respawns'];
                self::$mapItem[$v['id']]     = [];

                $cells[$v['id']] = $map_data['cellObject'];
            }

            $etime = microtime(true);
            $total = $etime - $stime;
            EchoLog(sprintf(PHP_EOL . '加载完成用时:%s 秒', $total));

            self::$mapIdInfos = array_column($res['list'], null, 'id');

            $this->CellData->initCells($cells);
        }

        return $res['total'];
    }

    public function getMapIds()
    {
        return array_column(self::$maps, 'id');
    }

    public function getMapById($mapId = null)
    {
        return $mapId ? self::$maps[$mapId] : self::$maps;
    }

    public function getDoorsMap($mapId, $x, $y)
    {
        if (!empty(self::$doorsMap[$mapId]['grid'][$x][$y])) {
            return self::$doorsMap[$mapId]['grid'][$x][$y];
        }

        return false;
    }

    public function getFileNameMap($file_name)
    {
        return self::$mapsFileName[$file_name];
    }

    public function setMapNpcs($mapId, $npcs)
    {
        self::$mapNpcs[$mapId] = $npcs;
    }

    public function getMapNpcs($mapId)
    {
        return self::$mapNpcs[$mapId];
    }

    public function getMapNpcIds($mapId)
    {
        if (!empty(self::$mapNpcs[$mapId])) {
            return array_column(self::$mapNpcs[$mapId], 'id');
        }
    }

    public function getMapNpcInfo($mapId, $id)
    {
        return !empty(self::$mapNpcs[$mapId][$id]) ? self::$mapNpcs[$mapId][$id] : false;
    }

    public function getMapPlayerIds($mapId)
    {
        return array_column(self::$mapPlayers[$mapId], 'id');
    }

    public function getMapPlayerInfo($mapId, $id)
    {
        return self::$mapPlayers[$mapId][$id];
    }

    public function setMapMonster($mapId, $object)
    {
        self::$mapMonsters[$mapId][] = $object;

        //同步到格子
        $oldCellId = $this->Cell->getCellId($object['current_location']['x'], $object['current_location']['y'], $object['map']['width'], $object['map']['height']);
        $this->CellData->setCellObject($mapId, $oldCellId, $object);
    }

    public function getMapMonster($mapId)
    {
        if (!empty(self::$mapMonsters[$mapId])) {
            return self::$mapMonsters[$mapId];
        } else {
            return false;
        }
    }

    public function getMapMonsterIds($mapId)
    {
        if (!empty(self::$mapMonsters[$mapId])) {
            return array_column(self::$mapMonsters[$mapId], 'id');
        } else {
            return false;
        }
    }

    public function getMapRespawns($mapId)
    {
        if (!empty(self::$mapRespawns[$mapId])) {
            return self::$mapRespawns[$mapId];
        } else {
            return false;
        }
    }

    public function setMapRespawns($mapId, $respawns = null)
    {
        if ($respawns) {
            self::$mapRespawns[$mapId] = $respawns;
        }
    }

    public function setMapPlayers($mapId, $info)
    {
        if (!empty(self::$mapPlayers[$mapId][$info['id']])) {
            //删除老格子
            $object    = self::$mapPlayers[$mapId][$info['id']];
            $oldCellId = $this->Cell->getCellId($object['current_location']['x'], $object['current_location']['y'], $object['map']['width'], $object['map']['height']);
            $this->CellData->delCellObject($mapId, $oldCellId, $object);
        }

        self::$mapPlayers[$mapId][$info['id']] = $info;

        //新增到格子
        $cellId = $this->Cell->getCellId($info['current_location']['x'], $info['current_location']['y'], $info['map']['width'], $info['map']['height']);
        $this->CellData->setCellObject($mapId, $cellId, $info);
    }

    public function getMapPlayers($mapId)
    {
        return !empty(self::$mapPlayers[$mapId]) ? self::$mapPlayers[$mapId] : false;
    }

    public function delMapPlayers($mapId, $id)
    {
        if (!empty(self::$mapPlayers[$mapId][$id])) {

            $info = self::$mapPlayers[$mapId][$id];
            unset(self::$mapPlayers[$mapId][$id]);

            //同步到格子
            $cellId = $this->Cell->getCellId($info['current_location']['x'], $info['current_location']['y'], $info['map']['width'], $info['map']['height']);
            $this->CellData->delCellObject($mapId, $cellId, $info);
        }
    }

    public function setMapItem($mapId, $point, $object)
    {
        self::$mapItem[$mapId][$point] = $object;
        //同步到格子 TODO
        
        
    }

    public function getMapItem($mapId, $point = null)
    {
        if (!$point) {
            return !empty(self::$mapItem[$mapId]) ? self::$mapItem[$mapId] : false;
        }
        return !empty(self::$mapItem[$mapId][$point]) ? self::$mapItem[$mapId][$point] : false;
    }

    public function delMapItem($mapId, $point)
    {
        if (!empty(self::$mapItem[$mapId][$point])) {
            unset(self::$mapItem[$mapId][$point]);
        }

        //同步到格子 TODO
    }
}
