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
        $x = $Point['x'];
        $y = $Point['y'];

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
        return ['x' => $x, 'y' => $y];
    }

    public function inRange($currentPoint, $point, $dataRange)
    {
        return AbsInt($currentPoint['x'] - intval($point['x'])) <= $dataRange && AbsInt(intval($currentPoint['y']) - intval($point['y'])) <= $dataRange;
    }
}
