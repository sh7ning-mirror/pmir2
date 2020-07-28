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

    public function setPlayer($fd, $object, $field = null)
    {
        if (!$field || empty(self::$players[$fd])) {
            self::$players[$fd] = $object;
        } else {
            foreach ($field as $key) {
                self::$players[$fd][$key] = isset($object[$key]) ? $object[$key] : null;
            }
        }

        //更新格子数据
        // if (!empty(self::$players[$fd]['map']['info']['id'])) {
        //     self::$players[$fd]['object_type'] = $this->Enum::ObjectTypePlayer;
        //     $this->MapData->setMapPlayers(self::$players[$fd]['map']['info']['id'], self::$players[$fd]);
        // }
    }

    public function delPlayer($fd)
    {
        if (!empty(self::$players[$fd])) {

            //更新格子数据
            if (!empty(self::$players[$fd]['map']['info']['id'])) {
                $this->MapData->delMapPlayers(self::$players[$fd]['map']['info']['id'], self::$players[$fd]['id']);
            }

            unset(self::$players[$fd]);
        }
    }
}
