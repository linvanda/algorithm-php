<?php

include_once './sort_test.php';

/**
 * 二分查找以及其变体
 * 二分查找要求数据是存储在数组中（利用其下标随机访问的特性），且排好序
 */

/**
 * 简单的二分查找
 * 元素不重复
*/
function simple_bsearch(SplFixedArray $arr, $need): int
{
    if (!count($arr)) {
        return -1;
    }

    if (count($arr) < 5) {
        foreach ($arr as $index => $item) {
            if ($item === $need) {
                return $index;
            }
        }

        return -1;
    }

    $low = 0;
    $high = count($arr) - 1;
    while ($low <= $high) {
        // 注意 mid 的计算：不能写成 ($low + $high) / 2，可能会导致整型越界。通过位操作符实现整除 2，且注意位操作优先级低于 +
        $mid = $low + (($high - $low) >> 1);
        if ($arr[$mid] === $need) {
            return $mid;
        }

        if ($arr[$mid] > $need) {
            $high = $mid - 1;
        } else {
            $low = $mid + 1;
        }
    }

    return -1;
}

/**
 * 找出第一个相等的元素
 */
function first_eq_bsearch(SplFixedArray $arr, $need): int
{
    $low = 0;
    $high = count($arr) - 1;
    while ($low <= $high) {
        $mid = $low + (($high - $low) >> 1);
        if ($arr[$mid] < $need) {
            $low = $mid + 1;
        } elseif ($arr[$mid] > $need) {
            $high = $mid - 1;
        } else {
            // 相等的情况下，需要判断该元素是否第一个，或者该元素的前一个元素比当前元素小，这样即表明该元素是第一个等于 $need 的元素
            // 如果不是第一个，则需要继续往左边找
            if ($mid === 0 || $arr[$mid - 1] < $need) {
                return $mid;
            }

            $high = $mid - 1;
        }
    }

    return -1;
}

/**
 * 找最后一个相等的元素
 */
function last_eq_bsearch(SplFixedArray $arr, $need): int
{
    $cnt = count($arr);
    $low = 0;
    $high = $cnt - 1;
    while ($low <= $high) {
        $mid = $low + (($high - $low) >> 1);
        if ($arr[$mid] < $need) {
            $low = $mid + 1;
        } elseif ($arr[$mid] > $need) {
            $high = $mid - 1;
        } else {
            // 相等的情况下，需要判断该元素是否最后个，或者该元素的后一个元素比当前元素大，这样即表明该元素是最后一个等于 $need 的元素
            // 如果不是最后一个，则需要继续往右边找
            if ($mid === $cnt - 1 || $arr[$mid + 1] > $need) {
                return $mid;
            }

            $low = $mid + 1;
        }
    }

    return -1;
}

/**
 * 找出最后一个小于等于目标值的元素位置
 */
function last_leq_bsearch(SplFixedArray $arr, $need): int
{
    $cnt = count($arr);
    $low = 0;
    $high = $cnt - 1;
    while ($low <= $high) {
        $mid = $low + (($high - $low) >> 1);
        if ($arr[$mid] > $need) {
            $high = $mid - 1;
        } else {
            if ($mid === $cnt - 1 || $arr[$mid + 1] > $need) {
                return $mid;
            }

            $low = $mid + 1;
        }
    }

    return -1;
}


$size = 500000;
$arr = new SplFixedArray($size);
// for ($i = 0; $i < $size; $i++) {
//     $arr[$i] = $i % 2 ? $i : $i - 1;
// }
$arr = SplFixedArray::fromArray([1,2,3,3,5,5,6]);

// test('first_eq_bsearch', 'first_eq_bsearch', $arr, 400000);
echo last_leq_bsearch($arr, 4);

