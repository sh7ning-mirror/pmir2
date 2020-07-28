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

    //大范围区间格子(临界值造成看不见东西,待改进)
    // public function getCellId($x, $y, $width = 100, $height = 100, $dataRange = 20)
    // {
    //     $page_x = ceil($width / $dataRange);
    //     $page_y = ceil($height / $dataRange);

    //     $num = 0;
    //     for ($i = 0; $i <= $page_x; $i++) {
    //         for ($j = 0; $j <= $page_y; $j++) {
    //             $num++;
    //             $l_x = $i * $dataRange;
    //             $r_x = $l_x + $dataRange;
    //             $l_y = $j * $dataRange;
    //             $r_y = $l_y + $dataRange;

    //             if ($x >= $l_x && $x <= $r_x && $y >= $l_y && $y <= $r_y) {
    //                 return $num;
    //             }
    //         }
    //     }
    // }

    //像素点格子
    public function getCellId($x, $y, $width = 100, $height = 100, $dataRange = 20)
    {
        return $x+$y*$width;
    }
}
