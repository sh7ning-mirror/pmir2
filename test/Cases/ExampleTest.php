<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Cases;

use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends HttpTestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
        $this->assertTrue(is_array($this->get('/')));
    }

    public function testStringToBytes()
    {
        $expected = function ($string)
        {
            $bytes = [];
            for ($i = 0; $i < strlen($string); $i++) {
                //遍历每一个字符 用ord函数把它们拼接成一个php数组
                $bytes[] = ord($string[$i]);
            }
            return $bytes;
        };

        $actual = function ($string)
        {
            return array_map('ord', str_split($string));
        };

        $this->assertSame($expected('从 v@ 额x'), $actual('从 v@ 额x'));
    }
}
