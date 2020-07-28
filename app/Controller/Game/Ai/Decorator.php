<?php

namespace App\Controller\Game\Ai;

use App\Controller\Game\Ai\Composite;
use App\Controller\Game\Ai\RunStatus;

class Decorator extends Composite
{

    private $delegate;
    private $child;

    public function __construct($d, Composite $c)
    {
        $this->delegate = $d;
        $this->child    = $c;
    }

    public function Update()
    {

        if (call_user_func($this->delegate, '') == true) {
            return $this->child->Update();
        }

        return RunStatus::FAILURE;
    }
}
