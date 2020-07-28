<?php
declare (strict_types = 1);

namespace App\Controller\Process;

use App\Controller\AbstractController;

/**
 *
 */
class ExpListData extends AbstractController
{
    public static $expList;

    public function __construct()
    {
        if (!self::$expList) {
            self::$expList = new \SplFixedArray(1000);
        }
    }

    public function init()
    {
        $file = config('settings_path') . '/Configs/ExpList.ini';

        if (!$fp = fopen($file, 'r')) {
            EchoLog(sprintf('打开文件失败 :%s', $file), 'e');
            return false;
        }

        $data = [];

        $num = 1;
        while (!feof($fp)) {

            if (!$txt = stream_get_line($fp, 1024, "\n")) {
                continue;
            }

            $txt = trim($txt);

            if (!$txt || $txt == ' ' || strpos($txt, 'Exp') !== false) {
                continue;
            }

            $txt = removeBOM($txt); //去除bom头

            $content = str_replace('Level', '', $txt);
            $content = explode('=', $content);

            if (!intval($content[0]) || $content[0] <= 0) {
                EchoLog(sprintf('等级设置错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            if (!intval($content[1]) || $content[1] <= 0) {
                EchoLog(sprintf('经验设置错误 content: %s; %s line: %s; ', $txt, $file, $num), 'e');
                return false;
            }

            $data[(int) $content[0] - 1] = $content[1];

            $num++;
        }

        fclose($fp);

        self::$expList = $data;
    }

    public function getExpList($level = null)
    {
        return $level ? self::$expList[$level] : self::$expList;
    }
}
