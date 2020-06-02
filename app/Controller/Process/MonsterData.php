<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class MonsterData extends AbstractController
{
    public static $monsterInfos;
    public static $monsterNameInfos;
    public static $dropInfoMap;

    public function __construct()
    {

    }

    public function setArray($max)
    {
        if (!self::$monsterInfos) {
            self::$monsterInfos = new \SplFixedArray($max);
        }
    }

    public function init()
    {
        $where = [
            'pageInfo' => false,
        ];
        $res = $this->CommonService->getList('monster', $where);

        if ($res['list']) {
            $this->setArray(getIndexMax($res['list'], 'id')+100);

            self::$monsterInfos     = array_column($res['list'], null, 'id');
            self::$monsterNameInfos = array_column($res['list'], null, 'name');
        }

        $this->loadMonsterDrop();

        return $res['total'];
    }

    public function loadMonsterDrop()
    {
        $path = config('settings_path');
        foreach (self::$monsterInfos as $k => $v) {
            $dropInfos = $this->loadDropFile($path . '/Envir/Drops/' . $v['name'] . ".txt");
            if ($dropInfos) {
                self::$dropInfoMap[$v['name']] = $dropInfos;
            }
        }
    }

    public function loadDropFile($file = '')
    {
        if (!$fp = fopen($file, 'r')) {
            EchoLog(sprintf('打开文件失败 :%s', $file), 'e');
            return false;
        }

        $data = [];

        $num = 1;
        while (!feof($fp)) {
            if (!$content = stream_get_line($fp, 1024, "\n")) {
                continue;
            }

            $content = trim($content);

            if (!$content || $content == ' ' || strpos($content, ';') !== false) {
                continue;
            }

            $content = removeBOM($content); //去除bom头

            $txt = preg_replace("/\s(?=\s)/", "\\1", $content);

            $content = explode(' ', $txt);

            if (count($content) != 3 && count($content) != 2) {
                EchoLog(sprintf('参数错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            $dropRate = explode('/', $content[0]);

            if (!intval($dropRate[0]) || $dropRate[0] <= 0) {
                EchoLog(sprintf('掉落分子错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            if (!intval($dropRate[1]) || $dropRate[1] <= 0) {
                EchoLog(sprintf('掉落分母错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            $info = [
                'low'            => $dropRate[0],
                'high'           => $dropRate[1],
                'item_name'      => $content[1],
                'quest_required' => false,
                'count'          => 1,
            ];

            if (count($content) == 3) {
                if (strtoupper($content[2]) == 'Q') {
                    $info['quest_required'] = true;
                } else {
                    $info['count'] = intval($content[2]);
                    if (!$info['count']) {
                        EchoLog(sprintf('掉落数量错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                        return false;
                    }
                }
            }

            $data[] = $info;

            $num++;
        }

        fclose($fp);

        return $data;
    }
}
