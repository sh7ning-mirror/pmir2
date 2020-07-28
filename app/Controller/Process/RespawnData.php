<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class RespawnData extends AbstractController
{
    public static $respawnInfos;

    public function __construct()
    {

    }

    public function setArray($max)
    {
        if (!self::$respawnInfos) {
            self::$respawnInfos = new \SplFixedArray($max);
        }
    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];

        $res = $this->CommonService->getList('respawn', $where);

        if ($res['list']) {
            $this->setArray(getIndexMax($res['list'], 'id') + 100);

            self::$respawnInfos = array_column($res['list'], null, 'id');
        }

        return $res['total'];
    }
}
