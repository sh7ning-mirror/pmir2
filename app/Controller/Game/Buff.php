<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Buff extends AbstractController
{
    public function newBuff($buffType, $object, $expireTime, $values)
    {
        return [
            'type'       => $buffType,
            'caster'     => $object,
            'visible'    => false, // 是否可见
            'object_id'  => 0,
            'expireTime' => time() + $expireTime, // 过期时间️
            'values'     => $values,
            'infinite'   => false, // 是否永久
            'paused'     => false,
        ];
    }

    public function has($BuffList, $b, $func)
    {
        foreach ($BuffList as $k => $v) {
            if (call_user_func_array($func, [$v, $b])) {
                return true;
            }
        }

        return false;
    }

    public function addBuff(&$BuffList, $b)
    {
        $BuffList[] = $b;
    }
}
