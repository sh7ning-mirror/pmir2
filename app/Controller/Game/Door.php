<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Door extends AbstractController
{
    public $Map;
    public $Index;
    public $State = 0; //0: closed, 1: opening, 2: open, 3: closing
    public $LastTick;
    public $Location;

    public function NewGrid($w, $h)
    {
        return [
            'W'    => $w,
            'H'    => $h,
            'Grid' => [],
        ];
    }

    public function newDoor()
    {
        return [
            'Map'      => [],
            'Index'    => null,
            'State'    => 0,
            'LastTick' => null,
            'Location' => [],
        ];
    }

    public function In($m, $loc)
    {
        return $loc['X'] < $m['doorsMap']['W'] && $loc['Y'] < $m['doorsMap']['H'];
    }

    public function Set($m, $loc, $d)
    {
        if ($this->In($m, $loc)) {

            if (empty($m['doorsMap']['Grid'][$loc['X']])) {
                $m['doorsMap']['Grid'][$loc['X']] = [];
            }

            $m['doorsMap']['Grid'][$loc['X']][$loc['Y']] = $d;

            return $m['doorsMap'];
        }
    }

    public function get($doorsMap, $point)
    {
        if (empty($doorsMap['Grid'][$point['X']][$point['Y']])) {
            return null;
        }

        return $doorsMap['Grid'][$point['X']][$point['Y']];
    }

    public function isOpen($door)
    {
        return $door['State'] == 2;
    }

    public function setOpen($map_id, $doorindex, $open)
    {
        // TODO 需要做定时器关门,并同步给玩家
    }
}
