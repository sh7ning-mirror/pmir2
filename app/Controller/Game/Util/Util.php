<?php
namespace App\Controller\Game\Util;

/**
 * 
 */
class Util
{
	public function stringEqualFold($cmdStr,$nowCmdStr)
	{
		if(is_array($nowCmdStr))
		{
			foreach ($nowCmdStr as $k => $v) 
			{
				if(strtoupper($cmdStr) == strtoupper($v))
				{
					return true;
				}
			}
		}elseif(strtoupper($cmdStr) == strtoupper($nowCmdStr)){
			return true;
		}

		return false;
	}
}