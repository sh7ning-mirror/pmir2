<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class SafeZoneData extends AbstractController
{
    public static $startPoints;
    public static $safeZoneInfos;

    public function __construct()
    {

    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];

        $res = $this->CommonService->getList('safe_zone', $where);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                if ($v['start_point']) {
                    self::$startPoints[$v['id']] = $v;
                }
            }
        }

        if ($res['list']) {
            self::$safeZoneInfos = array_column($res['list'], null, 'id');
        }

        return $res['total'];
    }

    public function randomStartPoint()
    {
        if(self::$startPoints)
        {
            return self::$startPoints[array_rand(self::$startPoints,1)];
        }else{
            return false;
        }
    }
}
