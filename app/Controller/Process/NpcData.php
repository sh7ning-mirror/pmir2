<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class NpcData extends AbstractController
{
    public static $npcInfos;
    public static $defaultNpc;
    public static $buyBack;

    public function __construct()
    {

    }

    public function setArray($max)
    {
        if (!self::$npcInfos) {
            self::$npcInfos = new \SplFixedArray($max);
        }
    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];

        $res = $this->CommonService->getList('npc', $where);

        if ($res['list']) {
            $this->setArray(getIndexMax($res['list'], 'id') + 100);

            self::$npcInfos = array_column($res['list'], null, 'id');
        }

        //初始化默认npc信息
        self::$defaultNpc = $this->Npc->newNpc(null, $this->Atomic->newObjectID(), [
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

        return $res['total'];
    }

    public function getDefaultNpc()
    {
        return self::$defaultNpc;
    }

    public function getNpcBuyBack($npcId)
    {
    	return self::$buyBack[$npcId];
    }

    public function getPlayerBuyBack($playerId, $npcId)
    {
        return !empty(self::$buyBack[$npcId][$playerId]) ? self::$buyBack[$npcId][$playerId] : [];
    }

    public function setPlayerBuyBack($playerId, $npcId, $object)
    {
        self::$buyBack[$npcId][$playerId][$object['id']] = $object;
    }

    public function removePlayerBuyBack($playerId, $npcId, $objectId)
    {
        if (!empty(self::$buyBack[$npcId][$playerId][$objectId])) {
            unset(self::$buyBack[$npcId][$playerId][$objectId]);
        }
    }
}
