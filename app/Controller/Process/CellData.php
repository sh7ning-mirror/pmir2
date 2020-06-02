<?php
declare (strict_types = 1);

namespace App\Controller\Process;

/**
 *
 */
class CellData
{
    public static $cells;

    public function __construct()
    {
        // if (!self::$cells) {
        //     self::$cells = new \SplFixedArray(1000);
        // }
    }

    public function initCells($cells)
    {
        self::$cells = $cells;
    }

    public function setCellObject($mapId, $cellId, $object)
    {
        self::$cells[$mapId][$cellId][] = $object;
    }

    public function getCellObject($mapId, $cellId)
    {
        if (!empty(self::$cells[$mapId][$cellId])) {
            return self::$cells[$mapId][$cellId];
        } else {
            return false;
        }
    }

    public function delCellObject($mapId, $cellId, $object)
    {
        if (!empty(self::$cells[$mapId][$cellId])) {

            foreach (self::$cells[$mapId][$cellId] as $k => $v) {
                if ($v['id'] == $object['id']) {
                    unset(self::$cells[$mapId][$cellId][$k]);
                    break;
                }
            }
        }
    }
}
