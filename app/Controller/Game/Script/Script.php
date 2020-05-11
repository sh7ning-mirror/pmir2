<?php
namespace App\Controller\Game\Script;

/**
 *
 */
class Script
{
    public $searchPaths;

    public function init()
    {
        $this->searchPaths = [
            config('settings_path') . '/Envir',
            config('settings_path') . '/Envir/NPCs',
        ];
    }

    public function removeBOM($str)
    {
        // if (strlen($str) >= 3) {
        //     if ($str[0] == 0xef && $str[1] == 0xbb && $str[2] == 0xbf) {
        //         return substr($str, 3);
        //     }
        // }
        // return $str;

    	if(strlen($str) >= 3)
    	{
    		$c0 = ord($str[0]);
	        $c1 = ord($str[1]);
	        $c2 = ord($str[2]);

	        if ($c0 == 0xFE && $c1 == 0xFF) {
	            // -- UTF-16BE BOM文件头: [0xFE, 0xFF],
	            $be = true;
	        } else if ($c0 == 0xFF && $c1 == 0xFE) {
	            // -- UTF-16LE BOM文件头: [0xFF, 0xFE],
	            $be = false;
	        } else if ($c0 == 0xEF && $c1 == 0xBB && $c2 == 0xBF) {
	            // -- UTF-8 BOM文件头: [0xEF, 0xBB, 0xBF]
	            $str = substr($str, 3);
	            return $str;
	        }
    	}
        
        return $str;
    }

    public function skipLine($str)
    {
        if ($str == "") {
            return true;
        }

        // 注释
        if ($str[0] == ';') {
            return true;
        }

        return false;
    }

    public function expandScript($lines)
    {
        $compiled = [];

        foreach ($lines as $line) {
            if ($this->skipLine($line)) {
                continue;
            }

            if ($line[0] == '#') {
                if (strpos($line, "#INSERT") === 0) {
                    preg_match('/\#INSERT\s*\[([^\n]+)\]\s*(@[^\n]+)/', $line, $match);

                    $insertLines = $this->readfile($match[1]);

                    if (!$insertLines) {
                        return false;
                    }

                    $insertLines = $this->expandScript($insertLines);
                    
                    if (!$insertLines) {
                        return false;
                    }

                    foreach ($insertLines as $v) 
                    {
                    	$compiled[] = $v;
                    }

                    continue;

                } elseif (strpos($line, "#INCLUDE") === 0) {
                    preg_match('/#INCLUDE\s*\[([^\n]+)\]\s*(@[^\n]+)/', $line, $match);

                    $insertLines = $this->loadScriptPage($this->fixSeparator($match[1]), strtoupper($match[2]));

                    if (!$insertLines) {
                        return false;
                    }

                    foreach ($insertLines as $v) 
                    {
                    	$compiled[] = $v;
                    }
                    continue;
                }
            }

            $compiled[] = $line;
        }

        return $compiled;
    }

    public function add($curPage)
    {
    	# code...
    }

    public function loadFile($file)
    {
        $lines = $this->readfile($file);
        if (!$lines) {
            return false;
        }

        $lines = $this->expandScript($lines);

        if (!$lines) {
            return false;
        }

        $ret = [];

        $curPage = [];

        foreach ($lines as $line) {
        	if($line[0] == '[')
        	{
        		preg_match('/^(\[[^\n]+\])\s*$/', $line, $match);

        		if(count($match) > 0)
        		{
        			if($curPage)
        			{
        				$ret['Pages'][strtoupper($curPage['Name'])] = $curPage;
        			}

        			$curPage = [
        				'Name' => $match[1],
        				'Lines' => []
        			];

        			continue;
        		}
        	}

        	if($curPage)
			{
				$curPage['Lines'][] = $line;
			}
        }

        if($curPage)
		{
			$ret['Pages'][strtoupper($curPage['Name'])] = $curPage;
		}

        return $ret;
    }

    public function readfile($file)
    {
        if (!$fp = fopen($this->fullpath($file), 'r')) {
            EchoLog(sprintf('打开文件失败 :%s', $file), 'e');
            return false;
        }

        $lines = [];
        while (!feof($fp)) {
            $lines[] = trim($this->removeBOM(stream_get_line($fp, 2048, "\n")));
        }
        fclose($fp);

        return $lines;
    }

    public function fullpath($file)
    {
        $this->init();
        $file = $this->fixSeparator($file);
        foreach ($this->searchPaths as $path) {
            $file_exists = $path . '/' . $file;
            if (file_exists($file_exists)) {
                break;
            }
        }
        return $file_exists;
    }

    public function fixSeparator($str)
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $str);
    }

    public function loadScriptPage($file, $page)
    {
        $lines = $this->readfile($file);

        if (!$lines) {
            return false;
        }

        $page = '[' . $page . ']';

        $stat = 0;

        $ret = [];
        foreach ($lines as $line) {
            if ($this->skipLine($line)) {
                continue;
            }
           
            switch ($stat) {
                case 0:
                    if ($line[0] == '[' && strpos(strtoupper($line), $page) === 0) {
                        $stat = 1;
                    }
                    break;

                case 1:
                    if ($line[0] == '{') {
                        $stat = 2;
                    }
                    break;

                case 2:
                    if ($line[0] == '}') {
                        return $ret;
                    }

                    $ret[] = $line;
                    break;
            }
        }

        EchoLog(sprintf('语法错误： %s; ', $this->fullpath($file)), 'e');
        return false;
    }
}
