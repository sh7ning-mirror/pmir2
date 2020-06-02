<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class MovementData extends AbstractController
{
    public static $movementInfos;

    public function __construct()
    {

    }

    public function setArray($max)
    {
        if (!self::$movementInfos) {
            self::$movementInfos = new \SplFixedArray($max);
        }
    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];

        $res = $this->CommonService->getList('movement', $where);

        if ($res['list']) {
            $this->setArray(getIndexMax($res['list'], 'id') + 100);

            self::$movementInfos = array_column($res['list'], null, 'id');
        }

        return $res['total'];
    }

    public function getMovements()
    {
        return self::$movementInfos;
    }

    public function getMovementInfos($id)
    {
        return !empty(self::$movementInfos[$id]) ? self::$movementInfos[$id] : false;
    }
}
