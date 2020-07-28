<?php

namespace App\Controller\Game\Ai;

use App\Controller\Game\Ai\RunStatus;

abstract class Composite
{
    public $Status;
    public $Children;
    private $currentIndex;

    public function __construct()
    {
        $this->Status       = RunStatus::RUNNING;
        $this->Children     = [];
        $this->currentIndex = 0;
    }

    abstract public function Update();

    public function Start()
    {
        $this->Status = RunStatus::RUNNING;
    }

    public function Stop()
    {
        if ($this->currentIndex >= count($this->Children) || $this->Status == RunStatus::INVALID) {
            $this->currentIndex = 0;
            $this->Status       = RunStatus::FAILURE;
        }
    }

    public function Tick()
    {
        if ($this->Status != RunStatus::RUNNING) {
            $this->Start();
        }
        
        $this->Status = $this->Update();

        return $this->Status;
    }
}
