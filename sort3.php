<?php

include_once './sort2.php';
include_once './sort_test.php';

/**
 * 时间复杂度为 O(n) 的线性排序：桶排序、计数排序、基数排序
 */

 /**
  * 桶排序
  * 适用于待排序的元素范围有限，可以较均匀地放入到 m 个桶中，且桶之间有明确的顺序，桶内排序完成后，可以按照桶的顺序将元素合并到一起形成有序列表
  * 每个桶中有 k = n/m 个元素，其时间复杂度是 m * klogk = nlogk，当 k 较小时，
  * logk 可看成常数，近而为 O(n)
  * 每个桶内部使用快速排序或者归并排序
  * 桶排序一般用在外部排序的场景，如对多个文件内容排序，然后合并
  */
 function bucket_sort(SplFixedArray $arr, $max)
 {
    /**
     * 这里假设 arr 中的是整数
     * 划分成 m = 500 个桶，每个桶平均放 k = max / 500 （整除）个元素
     * 每个桶放的数字范围是 [i * k, i * k + k)，超出范围的一律放入最后一个桶中
     * 数值 x 应放入第 x / k + 1 个桶，如果该位置超过桶范围，则放入最后一个桶中
     */
    $m = $max < 500 ? 1 : 500;
    $k = intval($max / $m);
    // 初始化桶
    $bucket = [];
    for($i = 0; $i < $m; $i++) {
        $bucket[$i] = [];
    }

    // 遍历源数组，将元素放入合适的桶中
    foreach ($arr as $item) {
        $idx = min(intval($item / $k), $m - 1);
        $bucket[$idx][] = $item;
    }

    // 对桶中元素执行归并排序
    $sort = new MergeSort();
    $offset = 0;
    for ($i = 0; $i < $m; $i++) {
        $bucket[$i] = SplFixedArray::fromArray($bucket[$i]);
        $sort->sort($bucket[$i]);
        // 合并
        for ($j = 0; $j < count($bucket[$i]); $j++) {
            $arr[$offset++] = $bucket[$i][$j];
        }
    }
 }

 /**
  * 计数排序
  * 计数排序思想和桶排序类似，和桶排序中每个桶放一定范围的数据不同，计数排序的每个计数放的是相同值的元素。
  * 计数排序适用于数据量很大但值的取值范围很小，且可以枚举，重复率高，此时我们将每个值作为一个计数标的。
  * 计数排序所排序的值必须是正整数或者能够转化成正整数，且不能太大（不能超过数组边界限制）。
  * 比如：将 100w 人按照年龄排序；100w 人按照分数排序等
  * 思想：将计数器按顺序排列，每个计数器的值对应该计数器（元素）出现的次数，那么第 m 个计数器的第一个元素出现的位置是 m - 1 个计数器总次数之和的下一位，最后
  *      则再加上本计数器对应的元素数。
  *      我们从左到右将计数器次数累加，便得到每个计数器对应的最后一个元素所在的位置。
  *      然后我们对原始数据从右到左扫描，每遇到一个元素，把它放到对应计数器所记录的位置上，然后将该计数器的值减 1，该值就表示倒数第二个元素所在的位置。
  *      注意：必须从右到左扫描来保证排序的稳定性
  * 例子：3, 4, 3, 2, 4，我们创建一个大小为 5 的数组，以这些值作为下标，得到 [0, 0, 1, 2, 2]，累加得到 [0, 0, 1, 3, 5]，表示 最后一个 3 应该出现在第 3 个位置
  */
 function count_sort(SplFixedArray $arr)
 {
    // 此处我们认为排序对象是正整数，而且 max 不会太大，不需要做处理，直接据此创建计数器
    $max = max($arr->toArray());
    $counter = SplFixedArray::fromArray(array_fill(0, $max + 1, 0));
    
    // 计数
    foreach ($arr as $item) {
        $counter[$item] += 1;
    }

    // 累加
    for ($i = 1; $i < count($counter); $i++) {
        $counter[$i] += $counter[$i - 1];
    }

    // 从后往前遍历原数组
    $tmpArr = new SplFixedArray(count($arr));
    for ($i = count($arr) - 1; $i >= 0; $i--) {
        // 将 arr[i] 放到 tmpArr 中正确的位置。下标从 0 开始，要减 1
        $tmpArr[$counter[$arr[$i]] - 1] = $arr[$i];
        // 将计数器值减 1
        $counter[$arr[$i]] = $counter[$arr[$i]] - 1;
    }

    // 拷贝回去（）
    for ($i = 0; $i < count($tmpArr); $i++) {
        $arr[$i] = $tmpArr[$i];
    }
 }

 /**
  * 基数排序
  * 基数排序基于桶排序或计数排序
  * 比如手机号排序，11 位手机号作为整型数据的话太大，不好用桶排序或者计数排序，可以截取手机号一部分，根据该部分排序，然后再截取另一部分
  * 具体做法：从右到左，一位一位的截取，每截取一位后采用计数排序，然后再截取下一个，继续排序，知道全部截取（11 位），则整个排序玩完毕
  * 为何要从右到左：类似手机号这种数据的特点是只要高位大则大，所以从低位向高位排，后面的排序操作不会导致乱序（反过来则不行）
  * 如果数据长度不一致，则需要在排序前补齐
  * 实现：略
  */


 // 测试
// $size = 500000;
// $max = 10000;
// $arr = new SplFixedArray($size);
// for ($i = 0; $i < $size; $i++) {
//     $arr[$i] = mt_rand(0, $max);
// }
// validate('bucket_sort', 'bucket_sort', 100000, $max);

// test('bucket_sort', 'bucket_sort', $arr, $max);

// $size = 500000;
// $max = 10000;
// $arr = new SplFixedArray($size);
// for ($i = 0; $i < $size; $i++) {
//     $arr[$i] = mt_rand(0, $max);
// }
// test(function ($data) {
//     (new MergeSort)->sort($data);
// }, 'merge sort', $arr);

// validate('count_sort', 'count_sort', 10000);
// 测试
$size = 500000;
$max = 100;
$arr = new SplFixedArray($size);
for ($i = 0; $i < $size; $i++) {
    $arr[$i] = mt_rand(0, $max);
}
test('count_sort', 'count_sort', $arr);

$size = 500000;
$max = 100;
$arr = new SplFixedArray($size);
for ($i = 0; $i < $size; $i++) {
    $arr[$i] = mt_rand(0, $max);
}
test('bucket_sort', 'bucket_sort', $arr, $max);