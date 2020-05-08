<?php
namespace App\Controller\Game;

/**
 *
 */
class MapObject
{
    //同步血值
    public function iMapObject_BroadcastHealthChange($m, $objectType = null)
    {
        if ($objectType == null && $objectType != getObject('Enum')::ObjectTypeMonster) {
            return false;
        }

        $percent = $m['HP'] / $m['MaxHP'] * 100;

        $msg = [
            'ObjectID' => $m['ID'],
            'Percent'  => $percent,
            'Expire'   => 5,
        ];

        return $msg;
    }
}
