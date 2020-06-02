<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class QuestData extends AbstractController
{
    public static $questInfos;

    public function __construct()
    {

    }

    public function setArray($max)
    {
        if (!self::$questInfos) {
            self::$questInfos = new \SplFixedArray($max);
        }
    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];

        $res = $this->CommonService->getList('quest', $where);

        if ($res['list']) {
            $this->setArray(getIndexMax($res['list'], 'id') + 100);
            self::$questInfos = array_column($res['list'], null, 'id');
        }

        return $res['total'];
    }
}
