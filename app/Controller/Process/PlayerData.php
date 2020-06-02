<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class PlayerData extends AbstractController
{
    public static $players;

    public function __construct()
    {

    }

    public function getPlayer($fd)
    {
        if (!empty(self::$players[$fd])) {
            return self::$players[$fd];
        }
        return false;
    }

    public function getIdPlayer($id)
    {
        if (self::$players) {
            foreach (self::$players as $k => $v) {
                if (!empty($v['id']) && $v['id'] == $id) {
                    return $v;
                }
            }
        }

        return false;
    }

    public function setPlayer($fd, $object)
    {
        self::$players[$fd] = $object;
    }

    public function delPlayer($fd)
    {
        if(!empty(self::$players[$fd]))
        {
            unset(self::$players[$fd]);
        }
    }
}
