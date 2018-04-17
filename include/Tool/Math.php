<?php

/**
 * @filename Math.php
 * @author lengdabao <lengdb@dodoca.net>
 * @datetime 2015/7/31 16:39
 * @description Math类库
 */
class Tool_Math
{

    /**
     * 浮点型数据比较
     * @param $left
     * @param $right
     * @param int $scale 修正位数
     * @return int 相等 :0,left>right :1,left<right :-1;
     */
    static public function floatCmp($left, $right, $scale = 4)
    {
        if(function_exists('bccomp'))
        {
            return bccomp($left,$right,$scale);
        }
        else
        {
            $scale = pow(10, intval($scale));
            $left = intval(floatval($left) * $scale);
            $right = intval(floatval($right) * $scale);
            return $left === $right ? 0 : ($left > $right ? 1 : -1);
        }

    }
}