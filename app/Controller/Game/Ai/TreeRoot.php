<?php

namespace App\Controller\Game\Ai;

use App\Controller\Game\Ai\Composite;
use App\Controller\Game\Ai\PrioritySelector;
use App\Controller\Game\Ai\RunStatus;

class TreeRoot
{
    private $hooks;
    private $rawHooks;
    private $throttle;

    public function __construct($pause = 1)
    {
        $this->throttle = $pause;
        $this->hooks    = new PrioritySelector();
        $this->rawHooks = [];
    }

    public function AddHook($name, Composite $h)
    {
        array_push($this->rawHooks, [
            'name'  => $name,
            'value' => $h,
        ]);

        array_push($this->hooks->Children, $h);
    }

    public function RemoveHook($name)
    {

    }

    public function RebuildTree()
    {
        $this->hooks = new PrioritySelector();

        foreach ($this->rawHooks as $h) {
            array_push($this->hooks->Children, $h['value']);
        }
    }

    private function Tick()
    {

        // while (true) {

            if ($this->hooks->Status != RunStatus::RUNNING) {
                $this->hooks->Start();
            }

            try {
                $status = $this->hooks->Tick();
            } catch (\Exception $e) {
                EchoLog(sprintf('行为树错误: %s[%s] in %s', $e->getMessage(), $e->getLine(), $e->getFile()), 'w');
                // break;
            }

            try {
                // EchoLog('行为树 Tick Complete', 'i');
                if($status != RunStatus::RUNNING)
                {
                    // break;
                }
                sleep($this->throttle);
            } catch (\Exception $e) {
                EchoLog(sprintf('行为树错误: %s[%s] in %s', $e->getMessage(), $e->getLine(), $e->getFile()), 'w');
                // break;
            }
        // }
    }

    public function Start()
    {
        try {
            $this->Tick();
        } catch (\Exception $e) {
            EchoLog(sprintf('行为树错误: %s[%s] in %s', $e->getMessage(), $e->getLine(), $e->getFile()), 'w');
            return;
        }
    }

    public function Stop()
    {
        $this->hooks->stop();
    }
}
