<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class MapObject extends AbstractController
{
    //同步血值
    public function iMapObject_BroadcastHealthChange($m, $objectType = null)
    {
        if ($objectType == null && $objectType != $this->Enum::ObjectTypeMonster) {
            return false;
        }

        $percent = $m['hp'] / $m['max_hp'] * 100;

        $msg = [
            'object_id' => $m['id'],
            'percent'   => $percent,
            'expire'    => 5,
        ];

        return $msg;
    }
}
