<?php

namespace App\Controller\Common;

use App\Controller\AbstractController;

/**
 *
 */
class Atomic extends AbstractController
{
    public function newObjectID()
    {
        if ($this->Server->worker_id < 0) {
            return $this->AtomicData->newObjectID();
        } else {
            return $this->Process->read(['AtomicData', 'newObjectID']);
        }
    }
}
