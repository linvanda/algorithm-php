<?php

function get_data($size)
{
    $arr = new SplFixedArray($size);

    for ($i = 0; $i < $size; $i++) {
        $arr[$i] = mt_rand(0, 1000);
    }

    return $arr;
}

function test($func, $name = '', $data = null, ...$args)
{
    $data = $data ?: get_data(100000);
    
    $start = microtime(true);
    $func($data, ...$args);
    $end = microtime(true);
    echo "$name,size:".count($data).",use time:" . ($end - $start) . "\n";
}

function validate($func, $name = '', $size = 100, ...$args)
{
    $data = get_data($size);
    $origData = $data->toArray();

    if ($size < 50) {
        echo "$name - before:" . print_r($data, true) . "\n";
    }
        $func($data, ...$args);
    if ($size < 50) {
        echo "$name - after:" . print_r($data, true) . "\n";
    }

    // 校验正确性
    sort($origData);
    for ($i = 0; $i < count($origData); $i++) {
        if ($origData[$i] !== $data[$i]) {
            // 排序算法有问题
            echo "-----sort error\n";
            return;
        }
    }

    echo "------sort ok\n";
}