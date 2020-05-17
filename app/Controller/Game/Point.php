<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Point extends AbstractController
{
    public function NextPoint($Point, $direction, $step)
    {
        $x = $Point['X'];
        $y = $Point['Y'];

        switch ($direction) {
            case $this->Enum::MirDirectionUp:
                $y = $y - $step;
                break;

            case $this->Enum::MirDirectionUpRight:
                $x = $x + $step;
                $y = $y - $step;
                break;

            case $this->Enum::MirDirectionRight:
                $x = $x + $step;
                break;

            case $this->Enum::MirDirectionDownRight:
                $x = $x + $step;
                $y = $y + $step;
                break;

            case $this->Enum::MirDirectionDown:
                $y = $y + $step;
                break;

            case $this->Enum::MirDirectionDownLeft:
                $x = $x - $step;
                $y = $y + $step;
                break;

            case $this->Enum::MirDirectionLeft:
                $x = $x - $step;
                break;

            case $this->Enum::MirDirectionUpLeft:
                $x = $x - $step;
                $y = $y - $step;
                break;
        }

        return $this->NewPoint($x, $y);
    }

    public function NewPoint($x, $y)
    {
        return ['X' => $x, 'Y' => $y];
    }

    public function inRange($currentPoint, $point, $dataRange)
    {
        // var_dump('---------------'.$currentPoint['X'].'_'.$point['X'].'___________'.$currentPoint['Y'].'_'.$point['Y']);

        return AbsInt($currentPoint['X'] - intval($point['X'])) <= $dataRange && AbsInt(intval($currentPoint['Y']) - intval($point['Y'])) <= $dataRange;
    }
}
