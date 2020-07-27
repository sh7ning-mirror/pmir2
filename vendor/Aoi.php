<?php

/**
 * Aoi算法测试
 */
class Aoi
{
    public $GID; //格子ID
    public $MinX; //格子的左边边界坐标
    public $MaxX; //格子的上边边界坐标
    public $MinY; //格子的下边边界坐标
    public $MaxY; //当前格子内 玩家/物体 成员的ID集合
    public $playerIDs; //当前格子内 玩家/物体 成员的ID集合
    public $pIDLock; //保护当前格子内容的map的锁

    //初始化格子
    public function NewGrid($gID, $minX, $maxX, $minY, $maxY)
    {
        $this->GID       = $gID;
        $this->MinX      = $minX;
        $this->MaxX      = $maxX;
        $this->MinY      = $minY;
        $this->MaxY      = $maxY;
        $this->playerIDs = [];
    }

    //给格子添加一个玩家
    public function add($playerID, $player)
    {
        $this->playerIDs[$playerID] = $player;
    }

    //从格子中删除一个玩家
    public function remove($playerID)
    {
        unset($this->playerIDs[$playerID]);
    }

    //得到当前格子所有的玩家ID
    public function getPlayerIDs()
    {
        return $this->playerIDs;
    }
}
