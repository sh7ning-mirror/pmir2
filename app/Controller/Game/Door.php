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
            'w'    => $w,
            'h'    => $h,
            'grid' => [],
        ];
    }

    public function newDoor()
    {
        return [
            'map'       => [],
            'index'     => null,
            'state'     => 0,
            'last_tick' => null,
            'location'  => [],
        ];
    }

    public function In($m, $loc)
    {
        return $loc['x'] < $m['width'] && $loc['y'] < $m['height'];
    }

    public function Set($m, $loc, $d)
    {
        if ($this->In($m, $loc)) {

            if (empty($m['doors_map']['grid'][$loc['x']])) {
                $m['doors_map']['grid'][$loc['x']] = [];
            }

            $m['doors_map']['grid'][$loc['x']][$loc['y']] = $d;

            return $m['doors_map'];
        }
    }

    public function get($map_id, $point)
    {
        return $this->GameData->getDoorsMap($map_id, $point['x'], $point['y']);
    }

    public function isOpen($door)
    {
        return !empty($door['state']) ? $door['state'] == 2 : false;
    }

    public function setOpen($map_id, $doorindex, $open)
    {
        // TODO 需要做定时器关门,并同步给玩家
    }
}
