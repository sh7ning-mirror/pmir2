<?php
namespace App\Controller\Game;

/**
 *
 */
class Cell
{
    public function NewCell($attr)
    {
        return [
            'point'     => [],
            'attribute' => $attr,
            'objects'   => [],
        ];
    }
}
