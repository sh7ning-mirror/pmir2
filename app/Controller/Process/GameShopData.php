<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class GameShopData extends AbstractController
{
    public static $gameShop;

    public function __construct()
    {
        // if (!self::$gameShop) {
        //     self::$gameShop = new \SplFixedArray(1000);
        // }
    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];

        $res = $this->CommonService->getList('game_shop_item', $where);

        if ($res['list']) {
            self::$gameShop = array_column($res['list'], null, 'id');
        }

        return $res['total'];
    }
}
