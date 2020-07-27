<?php

namespace App\Controller\Game\Ai;

use App\Controller\Game\Ai\Composite;
use App\Controller\Game\Ai\RunStatus;

class Sequence extends Composite {

	private $currentIndex;

	public function __construct() {
		$this->currentIndex = 0;
		$this->Children     = [];
		$numargs            = func_num_args();
		$args               = func_get_args();

		if($numargs == 0) {
			return;
		}

		foreach($args as $c) {
			array_push($this->Children, $c);
		}
	}

	public function Update() {

		$this->currentIndex = 0;

		while(true) {
			$rs = $this->Children[$this->currentIndex]->Tick();

			//任何失败都会杀死序列
			if($rs == RunStatus::FAILURE) {
				return RunStatus::FAILURE;
			}

			//如果全部完成，序列就是成功的
			if($this->currentIndex >= count($this->Children) - 1) {
				return RunStatus::SUCCESS;
			}

			$this->currentIndex++;
		}
	}
}