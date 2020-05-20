<?php

namespace App\Controller\Common;

use App\Controller\AbstractController;

/**
 *
 */
class Atomic extends AbstractController
{
    public static $Swoole_Atomic;
    public static $object_id;

    public function getNum()
    {
        $ObjectId = $this->Redis->get('ObjectId');

        if (!$ObjectId) {
            $ObjectId = 100000;
            $this->Redis->set('ObjectId', $ObjectId);
        }

        return $ObjectId;
    }

    public function get()
    {
        if (!self::$Swoole_Atomic) {
            self::$Swoole_Atomic = new \swoole_atomic($this->getNum());
        }

        return self::$Swoole_Atomic;
    }

    public function newObjectID()
    {
        $ObjectId = $this->get()->add(1);

        co(function () use ($ObjectId) {
            $this->Redis->set('ObjectId', $ObjectId);
        });

        return $ObjectId;
    }
}
