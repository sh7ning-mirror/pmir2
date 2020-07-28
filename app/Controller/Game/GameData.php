<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class GameData extends AbstractController
{
    public function loadGameData()
    {
        // $this->Redis->flushDB(); //清空当前库全部缓存
    }

    public function randomStartPoint()
    {
        return $this->SafeZoneData->randomStartPoint();
    }

    public function getMagicInfoByID($magic_id)
    {
        return $this->MagicData->getMagicInfoByID($magic_id);
    }

    public function getMagicInfoBySpell($spell)
    {
        return $this->MagicData->getMagicInfoBySpell($spell);
    }

    public function getExpList($level = null)
    {
        return $this->ExpListData->getExpList($level);
    }

    public function getStartItems()
    {
        return $this->ItemData->getStartItems();
    }

    public function getItemInfoById($ItemID)
    {
        return $this->ItemData->getItemInfoById($ItemID);
    }

    public function getMap($mapId = null)
    {
        return $this->MapData->getMapById($mapId);
    }

    public function getMapIds()
    {
        return $this->MapData->getMapIds();
    }

    public function getDoorsMap($mapId, $x, $y)
    {
        return $this->MapData->getDoorsMap($mapId, $x, $y);
    }

    public function getMapByName($file_name)
    {
        return $this->MapData->getFileNameMap($file_name);
    }

    public function getItemInfosIds()
    {
        return $this->ItemData->getItemInfosIds();
    }

    //根据名称获取物品
    public function getItemInfosName($name)
    {
        return $this->ItemData->getItemInfosName($name);
    }

    public function getMapNpcs($mapId)
    {
        return $this->MapData->getMapNpcs($mapId);
    }

    public function setMapNpcs($mapId, $npcs)
    {
        return $this->MapData->setMapNpcs($mapId, $npcs);
    }

    public function getMapNpcIds($mapId)
    {
        return $this->MapData->getMapNpcIds($mapId);
    }

    public function getMapNpcInfo($mapId, $id)
    {
        return $this->MapData->getMapNpcInfo($mapId, $id);
    }

    public function updateMapNpc($mapId, $id, $npc)
    {
        return $this->MapData->updateMapNpc($mapId, $id, $npc);
    }

    public function getMapPlayerIds($mapId)
    {
        return $this->MapData->getMapPlayerIds($mapId);
    }

    public function getMapPlayerInfo($mapId, $id)
    {
        return $this->MapData->getMapPlayerInfo($mapId, $id);
    }

    public function getMapPlayers($mapId)
    {
        return $this->MapData->getMapPlayers($mapId);
    }

    public function setMapPlayers($mapId, $object)
    {
        $object['object_type'] = $this->Enum::ObjectTypePlayer;

        $this->MapData->setMapPlayers($mapId, $object);
    }

    public function delMapPlayers($mapId, $p)
    {
        $this->MapData->delMapPlayers($mapId, $p['id']);
    }

    public function setMapItem($mapId, $object)
    {
        $point                 = $object['current_location']['x'] . '_' . $object['current_location']['y'];
        $object['object_type'] = $this->Enum::ObjectTypeItem;

        $this->MapData->setMapItem($mapId, $point, $object);
    }

    public function getMapItem($mapId, $point = null)
    {
        if (!$mapId) {
            return false;
        }

        if ($point) {
            $point = $point['x'] . '_' . $point['y'];
        }

        return $this->MapData->getMapItem($mapId, $point);
    }

    public function delMapItem($mapId, $point = null)
    {
        if (!$mapId || !$point) {
            return false;
        }

        $point = $point['x'] . '_' . $point['y'];

        $this->MapData->delMapItem($mapId, $point);
    }

    public function getMovements()
    {
        return $this->MovementData->getMovements();
    }

    public function getDefaultNpc()
    {
        return $this->NpcData->getDefaultNpc();
    }

    //怪物
    public function setMapMonster($mapId, $object)
    {
        $this->MapData->setMapMonster($mapId, $object);
    }

    public function getMapMonster($mapId)
    {
        return $this->MapData->getMapMonster($mapId);
    }

    //删除怪物
    public function delMapMonster($mapId, $monsterId)
    {
        $this->MapData->delMapMonster($mapId, $monsterId);
    }

    //根据id获取怪物信息
    public function getMapMonsterById($mapId, $monsterId)
    {
        return $this->MapData->getMapMonsterById($mapId, $monsterId);
    }

    //待生成
    public function getMapRespawns($mapId)
    {
        return $this->MapData->getMapRespawns($mapId);
    }

    public function setMapRespawns($mapId, $respawns)
    {
        return $this->MapData->setMapRespawns($mapId, $respawns);
    }

    //获取格子对象
    public function getCellObject($mapId, $cellId)
    {
        return $this->CellData->getCellObject($mapId, $cellId);
    }

    public function getPlayer($fd)
    {
        return $this->PlayerData->getPlayer($fd);
    }

    public function getIdPlayer($id)
    {
        return $this->PlayerData->getIdPlayer($id);
    }

    public function setPlayer($fd, $object, $field = null)
    {
        $object['object_type'] = $this->Enum::ObjectTypePlayer;
        $this->PlayerData->setPlayer($fd, $object, $field);
    }

    public function delPlayer($fd)
    {
        $this->PlayerData->delPlayer($fd);
    }

    //回购
    public function getNpcBuyBack($npcId)
    {
        return $this->NpcData->getNpcBuyBack($npcId);
    }

    //获取玩家在当前npc所有回购
    public function getPlayerBuyBack($playerId, $npcId)
    {
        return $this->NpcData->getPlayerBuyBack($playerId, $npcId);
    }

    //设置npc回购信息
    public function setPlayerBuyBack($playerId, $npcId, $object)
    {
        $this->NpcData->setPlayerBuyBack($playerId, $npcId, $object);
    }

    //删除npc回购信息
    public function removePlayerBuyBack($playerId, $npcId, $objectId)
    {
        $this->NpcData->removePlayerBuyBack($playerId, $npcId, $objectId);
    }

    //更新怪物数据
    public function updateMapMonster($mapId, $monsterId, $object, $field = null)
    {
        $this->MapData->updateMapMonster($mapId, $monsterId, $object, $field);
    }

    //获取怪物掉落
    public function getDrop($name)
    {
        return $this->MonsterData->getDrop($name);
    }
}
