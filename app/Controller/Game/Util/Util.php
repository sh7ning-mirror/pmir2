<?php
namespace App\Controller\Game\Util;

/**
 * 
 */
class Util
{
	public function stringEqualFold($cmdStr,$nowCmdStr)
	{
		for ($i=0; $i < strlen($nowCmdStr); $i++) { 
			if(equalFold($cmdStr,$nowCmdStr[$i]))
			{

			}
			return true;
		}

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

		return false
	}
}