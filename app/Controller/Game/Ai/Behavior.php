<?php
namespace App\Controller\Game\Ai;

use App\Controller\AbstractController;

/**
 *
 */
class Behavior extends AbstractController
{
    public function behavior($ai, $info)
    {
        $root = '';

        // switch ($ai) {
        //     case $ai == 1 || $ai == 2:
        //         $root = $this->Brains->deerBrain($info);
        //         break;

        //     case 3:
        //         $root = $this->Brains->treeBrain($info);
        //         break;

        //     case 4:
        //         $root = $this->Brains->spittingSpiderBrain($info);
        //         break;

        //     case $ai == 6 || $ai == 58:
        //         $root = $this->Brains->guardBrain($info);
        //         break;

        //     case 57:
        //         $root = $this->Brains->townArcherBrain($info);
        //         break;

        //     default:
        //         $root = $this->Brains->defaultBrain($info);
        //         break;
        // }

        $bt = [
            'timer' => time(),
            'root'  => $root,
        ];

        return $bt;
    }
}
