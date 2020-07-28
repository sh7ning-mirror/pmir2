<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class MagicData extends AbstractController
{
    public static $magicInfos;
    public static $magicSpellInfos;

    public function __construct()
    {

    }

    public function setArray($magicInfosMax, $magicSpellInfosMax)
    {
        if (!self::$magicInfos) {
            self::$magicInfos = new \SplFixedArray($magicInfosMax);
        }

        if (!self::$magicSpellInfos) {
            self::$magicSpellInfos = new \SplFixedArray($magicSpellInfosMax);
        }
    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('magic', $where);

        if ($res['list']) {
            $this->setArray(getIndexMax($res['list'], 'id')+100, getIndexMax($res['list'], 'spell')+100);

            self::$magicInfos      = array_column($res['list'], null, 'id');
            self::$magicSpellInfos = array_column($res['list'], null, 'spell');
        }

        return $res['total'];
    }

    public function getMagicInfoByID($magic_id)
    {
        return self::$magicInfos[$magic_id];
    }

    public function getMagicInfoBySpell($spell)
    {
        return self::$magicInfos[$spell];
    }

}
