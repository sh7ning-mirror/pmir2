<?php

namespace App\Controller\Common;

/**
 * 
 */
class Atomic
{
	public static $Swoole_Atomic;
	public static $object_id;


	public function getNum()
	{
		$ObjectId = getObject('Redis')->get('ObjectId');

		if(!$ObjectId)
		{
			$ObjectId = 100000;
			getObject('Redis')->set('ObjectId',$ObjectId);
		}

		return $ObjectId;
	}

	public function init()
	{
		if(!self::$Swoole_Atomic)
		{
			self::$Swoole_Atomic = new \swoole_atomic($this->getNum());
		}

		return self::$Swoole_Atomic;
	}

	public function newObjectID()
	{	
		return $this->init()->add(1);
	}
}