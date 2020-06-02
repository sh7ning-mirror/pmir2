<?php
declare (strict_types = 1);

namespace App\Controller\Process;

/**
 *
 */
class AtomicData
{
    public static $Swoole_Atomic;

    public function get()
    {
        if (!self::$Swoole_Atomic) {
            self::$Swoole_Atomic = new \swoole_atomic(100000);
        }

        return self::$Swoole_Atomic;
    }

    public function newObjectID()
    {
        return $this->get()->add(1);
    }
}
