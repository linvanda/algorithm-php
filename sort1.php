<?php

 /**
  * 冒泡排序
  * 平均时间复杂度O(n^2)，空间复杂度O(1)(原地排序)，稳定排序（值相同的元素排序前后顺序不变）
  * 像这种涉及到元素交换的，交换次数等于数组的初始逆序度
  * 有序度：数组中有多少对有序元素，如 2,4,3 的有序度是 2 (2-4, 2-3)；
  * 满有序度：数组排好序后的有序度，等于 n(n-1)/2，上面的等于 3*2/2 = 3；
  * 逆序度：等于满有序度 - 初始有序度，上面的逆序度等于 3 - 2 = 1；
  * 冒泡、插入等排序， 需要交换元素的次数等于数组的逆序度；
  */
function bubble_sort(SplFixedArray $arr)
{
    if ($arr->count() < 2) {
        return;
    }

    $cnt = $arr->count();
    // 外层循环每执行一次，数组右侧就多一个已排序的元素
    for ($i = 0; $i < $cnt; $i++) {
        // 因为第 $j 次要跟 $j + 1 次比较，所以这里第二层循环只需要到 $cnt - $j - 1 就行了
        for ($j = 0; $j < $cnt - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                // 交换位置
                $tmp = $arr[$j];
                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $tmp;
            }
        }
    }
}

/**
 * 插入排序
 */
function insert_sort(SplFixedArray $arr)
{
    if ($arr->count() < 2) {
        return;
    }

    $cnt = $arr->count();
    // 从第二个元素开始
    for ($i = 1; $i < $cnt; $i++) {
        // 先将当前需要处理的元素保存到临时变量中，防止被搬移过来的元素覆盖掉
        $val = $arr[$i];

        // 第二层从 $i 前面一个位置开始，逐渐往前面比较，直到找到合适的位置插入
        for ($j = $i - 1; $j >=0; $j--) {
            // 如果第 $j 个元素大于 $i 的，则将 $j 往后移，否则就讲 $val 插入到此位置
            // 因为该操作符合归纳法思想，能保证在处理第 i 个元素时，i 前面的已经是有序的
            if ($val < $arr[$j]) {
                // 元素往后移
                $arr[$j + 1] = $arr[$j];
            } else {
                break;
            }
        }

        // 将 val 放在 j + 1 的位置，注意走到这里，空位的索引比 j 要加 1
        $arr[$j + 1] = $val;
    }
}

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

        $div = $start + intval(($end - $start) / 2);

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
        $div = $start + intval(($end - $start) / 2);
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


function get_data($size)
{
    $arr = new SplFixedArray($size);
    $arr2 = clone $arr;

    for ($i = 0; $i < $size; $i++) {
        $arr[$i] = mt_rand(0, 1000);
    }

    return $arr;
}

function test($data, $func, $name = '')
{
    $start = microtime(true);
    $func($data);
    $end = microtime(true);
    echo "$name,size:{$data->count()},use time:" . ($end - $start) . "\n";
}

function validate($func)
{
    $data = get_data(10);
    echo "before:" . print_r($data, true) . "\n";
    $func($data);
    echo "after:" . print_r($data, true) . "\n";
}

// test(get_data(50000), 'bubble_sort');
// test(get_data(50000), 'insert_sort');
// validate(function ($data) {
//     (new MergeSort)->sort($data);
// });
// test(get_data(500000), function ($data) {
//     (new MergeSort)->sort($data);
// });
// validate(function ($data) {
//     (new QuickSort)->sort($data);
// });
// test(get_data(500000), function ($data) {
//     (new MergeSort)->sort($data);
// }, 'merge_sort');
// test(get_data(500000), function ($data) {
//     (new QuickSort)->sort($data);
// }, 'quick_sort');