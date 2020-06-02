<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class ItemData extends AbstractController
{
    public static $itemInfos;
    public static $startItems;
    public static $itemNameInfos;

    public function __construct()
    {

    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('item', $where);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {

                $v['is_tool_tip'] = false;

                if (($v['bools'] & 0x04) == 0x04) {
                    $v['class_based'] = true;
                } else {
                    $v['class_based'] = false;
                }

                if (($v['bools'] & 0x08) == 0x08) {
                    $v['level_based'] = true;
                } else {
                    $v['level_based'] = false;
                }

                if ($v['start_item']) {
                    self::$startItems[$v['id']] = $v;
                }
            }
        }

        if ($res['list']) {
            self::$itemInfos     = array_column($res['list'], null, 'id');
            self::$itemNameInfos = array_column($res['list'], null, 'name');
        }

        return [$res['total'], count(self::$startItems)];
    }

    public function getStartItems()
    {
        return self::$startItems;
    }

    public function getItemInfoById($itemId)
    {
        return self::$itemInfos[$itemId];
    }

    public function getItemInfosIds()
    {
        return array_column(self::$itemInfos, 'id');
    }
}
