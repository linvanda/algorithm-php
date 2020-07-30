<?php

include_once './sort_test.php';

/**
 * 时间复杂度 O(nlogn) 的排序：归并排序、快速排序
 */

/**
 * 归并排序
 * 取中间位置，将数组一分为二，然后将两部分分别再执行归并排序，直到不能再分，此时往回合并
 * 归并排序的时间复杂度是 O(nlgn)，空间复杂度是 O(n)，属于稳定排序
 */
class MergeSort
{
    public function sort(SplFixedArray $arr)
    {
        $this->merge_sort($arr, 0, $arr->count() - 1);
    }

    private function merge_sort(SplFixedArray $arr, $start, $end)
    {
        if ($end <= $start) {
            return;
        }

        $div = $start + (($end - $start) >> 1);

        // 左归并
        $this->merge_sort($arr, $start, $div);
        // 右归并
        $this->merge_sort($arr, $div + 1, $end);
        // 合并
        $this->merge($arr, $start, $end);
    }

    /**
     * 对区间数据进行合并
     * div 两边的数据已经通过归并排好序了
     * 其合并方式类似于并道交通中的拉链通行
     */
    private function merge($arr, $start, $end)
    {
        // 创建一个临时数组存储合并后的数据，此处导致归并排序的空间复杂度是 O(n)
        $tmpArr = new SplFixedArray($end - $start + 1);
        $div = $start +  (($end - $start) >> 1);
        $i = $start;
        $j = $div + 1;
        $k = 0;
        while ($i <= $div && $j <= $end) {
            // 将两个数组中每个元素比较，取小的放入 tmpArr 中
            // 注意这里只有 j 小于 i 才放入 j，否则放入 i，保证排序的稳定性
            if ($arr[$j] < $arr[$i]) {
                $tmpArr[$k++] = $arr[$j++];
            } else {
                $tmpArr[$k++] = $arr[$i++];
            }
        }

        // 将剩余数组中的继续搬入 tmpArr 中
        $start2 = $i;
        $end2 = $div;
        if ($j <= $end) {
            $start2 = $j;
            $end2 = $end;
        }

        while ($start2 <= $end2) {
            $tmpArr[$k] = $arr[$start2];
            $start2++;
            $k++;
        }

        // 将合并后的数组（已排好序）拷贝到原数组中
        for ($i = 0; $i < $tmpArr->count(); $i++) {
            $arr[$start + $i] = $tmpArr[$i];
        }
    }
}

/**
 * 快速排序
 * 从数组中找一个支点（pivot），将小于该支点的放到其左边，大于支点的放到右边，然后再对左右两边的子集做同样处理
 * 快速排序是不稳定排序，无法保证两个相等的元素排序前后位置不变
 * 归并排序是自下而上的，最先排好序的是最下面的，当上层进行合并时，待合并的两部分是已经排好序的，是从部分到整体逐步有序的
 * 快速排序是自上而下的，先在上层对三部分进行整体上排序（左边小于支点小于右边），然后往下深入，直到最小单元，是从整体到部分逐步有序的
 * 优化点：
 *  1. 为了防止数组过大而递归层次过深导致栈内存溢出，可以使用数据结构栈来模拟递归调用
 *  2. 对于数据量很小（<=4)的可以退化成插入排序
 *  3. 可以用“三点取中”或随机数的方式避免极端情况下复杂度退化到 O(n^2)
 */
class QuickSort
{
    public function sort(SplFixedArray $arr)
    {
        $this->qsort($arr, 0, count($arr) - 1);
    }

    private function qsort(SplFixedArray $arr, int $start, int $end)
    {
        // 只有一个元素，不能再分
        if ($start >= $end) {
            return;
        }

        // 先找出支点位置
        $point = $this->partition($arr, $start, $end);

        // 对支点的左右各继续 qsort
        $this->qsort($arr, $start, $point - 1);
        $this->qsort($arr, $point + 1, $end);
    }

    private function partition(SplFixedArray $arr, int $start, int $end): int
    {
        // 取最后一个元素作为支点（为了保证排序稳定性，可以改成取随机位置）
        $pivot = $arr[$end];
        $point = $start;
        for ($i = $start; $i <= $end; $i++) {
            // 如果第 i 个元素比 pivot 小，则将该元素和 point 位置的元素交换，并将 i 后移一位
            // 这一步的目的是将比 pivot 小的元素移到左边（point 的左边，point 最后就是 pivot 的新位置）
            if ($arr[$i] < $pivot) {
                $tmp = $arr[$i];
                $arr[$i] = $arr[$point];
                $arr[$point] = $tmp;
                $point++;
            }
        }

        // 最后，比较 point 位置的值和 pivot 的大小
        if ($point < $end && $arr[$point] > $pivot) {
            $arr[$end] = $arr[$point];
            $arr[$point] = $pivot;
        }

        // 返回 pivot 所在的位置
        return $point;
    }
}
