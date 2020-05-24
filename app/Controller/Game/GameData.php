<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class GameData extends AbstractController
{
    public $npcInfos;
    public $respawnInfos;
    public $safeZoneInfo;
    public static $dataMonsters;
    public static $monsters;
    public static $maps;

    public function loadGameData()
    {
        $this->Redis->flushDB(); //清空当前库全部缓存

        $gameShopItems      = $this->gameShopItems();
        $itemInfos          = $this->itemInfos();
        $magicInfos         = $this->magicInfos();
        $monsterInfos       = $this->monsterInfos();
        $movementInfos      = $this->movementInfos();
        $defaultNPC         = $this->defaultNPC();
        $this->npcInfos     = $this->npcInfos();
        $questInfos         = $this->questInfos();
        $this->respawnInfos = $this->respawnInfos();
        $this->safeZoneInfo = $this->safeZoneInfos();
        $mapInfos           = $this->mapInfos();

        EchoLog(sprintf('数据初始化加载 商品:%s 物品:%s 技能:%s 地图:%s 怪物:%s 怪物巡逻:%s NPC:%s 任务:%s 重新生成:%s 安全区:%s',
            $gameShopItems['total'],
            $itemInfos['total'],
            $magicInfos['total'],
            $mapInfos['total'],
            $monsterInfos['total'],
            $movementInfos['total'],
            $this->npcInfos['total'],
            $questInfos['total'],
            $this->respawnInfos['total'],
            $this->safeZoneInfo['total']
        ), null, true);

        $this->loadMonsterDrop($monsterInfos['list']); //怪物掉落

        $this->loadExpList(); //升级经验
    }

    public function gameShopItems()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('game_shop_item', $where);

        $this->Redis->set('gameShopItems', json_encode($res['list'], JSON_UNESCAPED_UNICODE));
        return $res;
    }

    public function itemInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('item', $where);

        $startItems      = [];
        $itemIDInfoMap   = [];
        $itemNameInfoMap = [];

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {

                $v['is_tool_tip'] = false;

                if (($v['bools'] & 0x04) == 0x04) {
                    $v['class_based'] = true;
                } else {
                    $v['class_based'] = false;
                }

                if (($v['bools'] & 0x08) == 0x08) {
                    $v['level_based'] = true;
                } else {
                    $v['level_based'] = false;
                }

                if ($v['start_item']) {
                    $startItems[] = $v;
                }

                $itemIDInfoMap[$v['id']]     = $v;
                $itemNameInfoMap[$v['name']] = $v;
            }
        }

        $this->Redis->set('itemInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        $this->Redis->set('startItems', json_encode($startItems, JSON_UNESCAPED_UNICODE));

        $this->Redis->set('itemIDInfoMap', json_encode($itemIDInfoMap, JSON_UNESCAPED_UNICODE));

        $this->Redis->set('itemNameInfoMap', json_encode($itemNameInfoMap, JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function magicInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('magic', $where);

        $magicIDInfoMap = [];

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                $magicIDInfoMap[$v['id']] = $v;
            }
        }

        $this->Redis->set('magicInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        $this->Redis->set('magicIDInfoMap', json_encode($magicIDInfoMap, JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function mapInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('map', $where);

        $mapIDInfoMap = [];
        $Maps         = [];

        $path = config('settings_path');

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
                $m['info']      = $v;

                $Maps[$v['id']] = $m;

                $map_data = $this->Map->initAll($m, $this->npcInfos['list'], $this->respawnInfos['list'], $this->safeZoneInfo['list']);

                //生成地图缓存
                $players_key  = 'map:players_' . $v['id'];
                $npcs_key     = 'map:npcs_' . $v['id'];
                $monsters_key = 'map:monsters_' . $v['id'];
                $respawns_key = 'map:respawns_' . $v['id'];
                $item_key     = 'map:item_' . $v['id'];

                co(function () use ($players_key) {
                    $this->Redis->set($players_key, json_encode([], JSON_UNESCAPED_UNICODE));
                });

                co(function () use ($npcs_key, $map_data) {
                    $this->Redis->set($npcs_key, json_encode($map_data['npc'], JSON_UNESCAPED_UNICODE));
                });

                co(function () use ($monsters_key, $map_data) {
                    $this->Redis->set($monsters_key, json_encode($map_data['monsters'], JSON_UNESCAPED_UNICODE));
                });

                co(function () use ($respawns_key, $map_data) {
                    $this->Redis->set($respawns_key, json_encode($map_data['respawns'], JSON_UNESCAPED_UNICODE));
                });

                co(function () use ($item_key) {
                    $this->Redis->set($item_key, json_encode([], JSON_UNESCAPED_UNICODE));
                });
            }

            $etime = microtime(true);
            $total = $etime - $stime;
            EchoLog(sprintf(PHP_EOL . '加载完成用时:%s 秒', $total));
        }

        // $this->Redis->set('mapInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        $this->Redis->set('Maps', json_encode($Maps, JSON_UNESCAPED_UNICODE));

        $this->Redis->set('mapIDInfoMap', json_encode($mapIDInfoMap, JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function monsterInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('monster', $where);

        $monsterIDInfoMap   = [];
        $monsterNameInfoMap = [];

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                $monsterIDInfoMap[$v['id']]     = $v;
                $monsterNameInfoMap[$v['name']] = $v;
            }
        }

        $this->Redis->set('monsterInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        $this->Redis->set('monsterIDInfoMap', json_encode($monsterIDInfoMap, JSON_UNESCAPED_UNICODE));

        $this->Redis->set('monsterNameInfoMap', json_encode($monsterNameInfoMap, JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function movementInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('movement', $where);

        $this->Redis->set('movementInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function npcInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('npc', $where);

        $this->Redis->set('npcInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function questInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('quest', $where);

        $this->Redis->set('questInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function respawnInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('respawn', $where);

        $this->Redis->set('respawnInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function safeZoneInfos()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('safe_zone', $where);

        $startPoints = [];

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                if ($v['start_point']) {
                    $startPoints[] = $v;
                }
            }
        }

        $this->Redis->set('startPoints', json_encode($startPoints, JSON_UNESCAPED_UNICODE));

        $this->Redis->set('safeZoneInfos', json_encode($res['list'], JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function loadMonsterDrop($monsterInfos)
    {
        $dropInfoMap = [];
        $path        = config('settings_path');
        foreach ($monsterInfos as $k => $v) {
            $dropInfos = $this->loadDropFile($path . '/Envir/Drops/' . $v['name'] . ".txt");
            if ($dropInfos) {
                $dropInfoMap[$v['name']] = $dropInfos;
            }
        }

        $this->Redis->set('dropInfoMap', json_encode($dropInfoMap, JSON_UNESCAPED_UNICODE));
    }

    public function loadDropFile($file = '')
    {
        if (!$fp = fopen($file, 'r')) {
            EchoLog(sprintf('打开文件失败 :%s', $file), 'e');
            return false;
        }

        $data = [];

        $num = 1;
        while (!feof($fp)) {
            $content = trim(stream_get_line($fp, 1024, "\n"));
            // $content = trim(fgets($fp, 1024));
            if (!$content || $content == ' ' || strpos($content, ';') !== false) {
                continue;
            }

            $content = removeBOM($content); //去除bom头

            $txt = preg_replace("/\s(?=\s)/", "\\1", $content);

            $content = explode(' ', $txt);

            if (count($content) != 3 && count($content) != 2) {
                EchoLog(sprintf('参数错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            $dropRate = explode('/', $content[0]);

            if (!intval($dropRate[0]) || $dropRate[0] <= 0) {
                EchoLog(sprintf('掉落分子错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            if (!intval($dropRate[1]) || $dropRate[1] <= 0) {
                EchoLog(sprintf('掉落分母错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            $info = [
                'low'            => $dropRate[0],
                'high'           => $dropRate[1],
                'item_name'      => $content[1],
                'quest_required' => false,
                'count'          => 1,
            ];

            if (count($content) == 3) {
                if (strtoupper($content[2]) == 'Q') {
                    $info['quest_required'] = true;
                } else {
                    $info['count'] = intval($content[2]);
                    if (!$info['count']) {
                        EchoLog(sprintf('掉落数量错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                        return false;
                    }
                }
            }

            $data[] = $info;

            $num++;
        }

        fclose($fp);

        return $data;
    }

    public function loadExpList()
    {
        $file = config('settings_path') . '/Configs/ExpList.ini';

        if (!$fp = fopen($file, 'r')) {
            EchoLog(sprintf('打开文件失败 :%s', $file), 'e');
            return false;
        }

        $data = [];

        $num = 1;
        while (!feof($fp)) {
            $txt = trim(stream_get_line($fp, 1024, "\n"));

            if (!$txt || $txt == ' ' || strpos($txt, 'Exp') !== false) {
                continue;
            }

            $txt = removeBOM($txt); //去除bom头

            $content = str_replace('Level', '', $txt);
            $content = explode('=', $content);

            if (!intval($content[0]) || $content[0] <= 0) {
                EchoLog(sprintf('等级设置错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            if (!intval($content[1]) || $content[1] <= 0) {
                EchoLog(sprintf('经验设置错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            $data[(int) $content[0] - 1] = $content[1];

            $num++;
        }
        fclose($fp);
        $this->Redis->set('expList', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function randomStartPoint()
    {
        $startPoints = json_decode($this->Redis->get('startPoints'), true);

        return $startPoints[mt_rand(0, count($startPoints) - 1)];
    }

    public function getMagicInfoByID($magic_id)
    {
        $magicIDInfoMap = json_decode($this->Redis->get('magicIDInfoMap'));

        return !empty($magicIDInfoMap[$magic_id]) ? $magicIDInfoMap[$magic_id] : [];
    }

    public function getMagicInfoBySpell($spell)
    {
        $magicIDInfoMap = json_decode($this->Redis->get('magicIDInfoMap'), true);

        foreach ($magicIDInfoMap as $k => $v) {
            if ($v['spell'] == $spell) {
                return $v;
            }
        }

        return false;
    }

    public function getExpList($level = null)
    {
        $expList = json_decode($this->Redis->get('expList'), true);
        return $level == null ? $expList : $expList[$level] ?: 1;
    }

    public function getStartItems()
    {
        return json_decode($this->Redis->get('startItems'), true);
    }

    public function getItemInfoByID($ItemID)
    {
        $itemIDInfoMap = json_decode($this->Redis->get('itemIDInfoMap'), true);
        return $itemIDInfoMap[$ItemID] ?: null;
    }

    public function getMaps()
    {
        if (!self::$maps) {
            self::$maps = json_decode($this->Redis->get('Maps'), true);
        }

        return self::$maps;
    }

    public function getMap($mapId = null)
    {
        $Maps = $this->getMaps();

        if ($mapId == null) {
            return $Maps;
        }

        return $Maps[$mapId] ?: null;
    }

    public function getMapByName($file_name)
    {
        $Maps = $this->getMaps();

        foreach ($Maps as $k => $v) {
            if ($v['info']['file_name'] == strtoupper(trim($file_name))) {
                return $v;
            }
        }

        return false;
    }

    public function getItemInfos()
    {
        return json_decode($this->Redis->get('itemInfos'), true);
    }

    public function getRealItem($origin, $level, $job, $itemList)
    {
        if (!empty($origin['class_based']) && !empty($origin['level_based'])) {
            return $this->getClassAndLevelBasedItem($origin, $job, $level, $itemList);
        }
        if (!empty($origin['class_based'])) {
            return $this->getClassBasedItem($origin, $job, $itemList);
        }
        if (!empty($origin['level_based'])) {
            return $this->getLevelBasedItem($origin, $level, $itemList);
        }

        return $origin;
    }

    public function getClassAndLevelBasedItem($origin, $job, $level, $itemList)
    {
        $output = $origin;

        for ($i = 0; $i < count($itemList); $i++) {
            $info = $itemList[$i];
            if (strpos($info['name'], $origin['name']) === 0) {

                if ($info['required_class'] == (1 << $job)) {
                    if ($info['required_type'] == $this->Enum::RequiredTypeLevel && $info['required_amount'] <= $level && $output['required_amount'] <= $info['required_amount'] && $origin['required_gender'] == $info['required_gender']) {
                        $output = $info;
                    }
                }
            }
        }
        return $output;
    }

    public function getClassBasedItem($origin, $job, $itemList)
    {
        for ($i = 0; $i < count($itemList); $i++) {
            $info = $itemList[$i];
            if (strpos($info['name'], $origin['name']) === 0) {
                if ($info['required_class'] == (1 << $job) && $origin['required_gender'] == $info['required_gender']) {
                    return $info;
                }
            }
        }
        return $origin;
    }

    public function getLevelBasedItem($origin, $level, $itemList)
    {
        $output = $origin;

        for ($i = 0; $i < count($itemList); $i++) {
            $info = $itemList[$i];
            if (strpos($info['name'], $origin['name']) === 0) {
                if ($info['required_type'] == $this->Enum::RequiredTypeLevel && $info['required_amount'] <= $level && $output['required_amount'] < $info['required_amount'] && $origin['required_gender'] == $info['required_gender']) {
                    $output = $info;
                }
            }
        }
        return $output;
    }

    //获取地图所有npc
    public function getMapNpc($map_id)
    {
        $key = 'map:npcs_' . $map_id;
        return json_decode($this->Redis->get($key), true);
    }

    //获取地图中人物
    public function getMapPlayers($map_id)
    {
        $key = 'map:players_' . $map_id;
        return json_decode($this->Redis->get($key), true);
    }

    //添加地图人物
    public function setMapPlayers($map_id, $p)
    {
        $key = 'map:players_' . $map_id;

        $mapPlayers           = $this->getMapPlayers($map_id) ?: [];
        $mapPlayers[$p['id']] = [
            'fd'          => $p['fd'],
            'id'          => $p['id'],
            'name'        => $p['name'],
            'object_type' => $this->Enum::ObjectTypePlayer,
        ];

        $this->Redis->set($key, json_encode($mapPlayers, JSON_UNESCAPED_UNICODE));
    }

    public function setMapItem($map_id, $object)
    {
        $key     = 'map:item_' . $map_id;
        $mapItem = $this->getMapItem($map_id) ?: [];
        $point   = $object['current_location']['x'] . '_' . $object['current_location']['y'];

        $object['object_type'] = $this->Enum::ObjectTypeItem;

        $mapItem[$point] = $object;

        $this->Redis->set($key, json_encode($mapItem, JSON_UNESCAPED_UNICODE));
    }

    public function getMapItem($map_id, $point = null)
    {
        $key   = 'map:item_' . $map_id;
        $items = json_decode($this->Redis->get($key), true);

        if ($point) {
            return !empty($items[$point['x'] . '_' . $point['y']]) ? $items[$point['x'] . '_' . $point['y']] : '';
        } else {
            return $items;
        }
    }

    public function delMapItem($map_id, $point = null)
    {
        if (!$map_id || !$point) {
            return;
        }

        $key   = 'map:item_' . $map_id;
        $items = json_decode($this->Redis->get($key), true);

        if (!empty($items[$point['x'] . '_' . $point['y']])) {
            unset($items[$point['x'] . '_' . $point['y']]);
            $this->Redis->set($key, json_encode($items, JSON_UNESCAPED_UNICODE));
        }
    }

    //删除地图人物
    public function delMapPlayers($map_id, $p)
    {
        $key        = 'map:players_' . $map_id;
        $mapPlayers = $this->getMapPlayers($map_id);

        $mapPlayers[$p['id']] = null;
        unset($mapPlayers[$p['id']]);

        $this->Redis->set($key, json_encode($mapPlayers, JSON_UNESCAPED_UNICODE));
    }

    public function getMovementInfos()
    {
        return json_decode($this->Redis->get('movementInfos'), true);
    }

    public function defaultNPC()
    {
        $defaultNPC = $this->Npc->newNpc(null, $this->Atomic->newObjectID(), [
            'map_id'         => '',
            'file_name'      => '00Default',
            'name'           => 'DefaultNPC',
            'chinese_name'   => '',
            'location_x'     => '',
            'location_y'     => '',
            'rate'           => '',
            'image'          => '',
            'time_visible'   => '',
            'hour_start'     => '',
            'minute_start'   => '',
            'hour_end'       => '',
            'minute_end'     => '',
            'min_lev'        => '',
            'max_lev'        => '',
            'day_of_week'    => '',
            'class_required' => '',
            'flag_needed'    => '',
            'conquest'       => '',
        ]);

        $this->Redis->set('defaultNPC', json_encode($defaultNPC, JSON_UNESCAPED_UNICODE));
    }

    public function getItemInfoByName($name = '')
    {
        # code...
    }

    public function getDefaultNPC()
    {
        return json_decode($this->Redis->get('defaultNPC'), true);
    }

    public function getMonsterIDInfoMap()
    {
        if (!self::$dataMonsters) {
            self::$dataMonsters = json_decode($this->Redis->get('monsterIDInfoMap'), true);
        }

        return self::$dataMonsters;
    }

    public function getMonsterInfoByID($m_id)
    {
        $monsterList = $this->getMonsterIDInfoMap();

        return !empty($monsterList[$m_id]) ? $monsterList[$m_id] : null;
    }

    public function getMapMonsters($map_id)
    {
        if (!self::$monsters) {
            self::$monsters = json_decode($this->Redis->get('map:monsters_' . $map_id), true);
        }

        return self::$monsters;
    }

    public function setMapRespawnMonster($map_id, $monster)
    {
        $monsters_key  = 'map:monsters_' . $map_id;
        $monsterList   = json_decode($this->Redis->get($monsters_key), true);
        $monsterList[] = $monster;
        $this->Redis->set($monsters_key, json_encode($monsterList, JSON_UNESCAPED_UNICODE));
    }

    public function getMapMonster($map_id)
    {
        return $this->getMapMonsters($map_id);
    }
}
