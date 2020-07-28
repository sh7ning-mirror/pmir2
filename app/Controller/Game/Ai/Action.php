<?php

namespace App\Controller\Game\Ai;
use App\Controller\Game\Ai\Composite;

class Action extends Composite {

	private $delegate;

	public function __construct($d) {
		$this->delegate = $d;
	}

	public function Update() {
		$this->Status = call_user_func($this->delegate, '');
		return $this->Status;
	}

	public function getStatus()
	{
		return $this->Status; 
	}
}