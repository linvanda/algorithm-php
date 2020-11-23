<?php

interface IComparable
{
    /**
     * 元素比较，返回：
     * 当前对象小于 $other 返回负数，相等返回 0，大于则返回正数
     */
    public function compare(IComparable $other): int;
}

 /**
  * 有序数组
  * 数组中元素根据大小升序排列
  * 有序数组查找元素很快，但插入元素可能需要移位，适用于查询频率远大于插入频率的场景
  * 添加到有序数组的元素必须实现 IComparable 接口
  * 注意：有序数组实现了迭代器，可以 foreach 遍历，但由于内部元素是已排序的，和加入的顺序可能不同
  */
 class OrderedArray implements \Iterator
 {
    private $data;
    private $pos;

    public function __construct()
    {
        $this->data = [];
        $this->pos = 0;
    }

    /**
     * 获取 $index 位置的值
     */
    public function get(int $index): ?IComparable
    {
        return $this->data[$index] ?? null;
    }

    /**
     * 元素个数
     */
    public function size(): int
    {
        return count($this->data);
    }

    /**
     * 查找 $item 所在的位置，如果没有，则返回 -1
     */
    public function search(IComparable $item): int
    {
        $arr = $this->data;
        $cnt = count($arr);
        $low = 0;
        $high = $cnt - 1;
        while ($low <= $high) {
            $mid = $low + (($high - $low) >> 1);
            $compare = $arr[$mid]->compare($item);
            if ($compare === 0) {
                return $mid;
            } elseif ($compare > 0) {
                $high = $mid - 1;
            } else {
                $low = $mid + 1;
            }
        }

        return -1;
    }

    /**
     * 如果值已经存在（通过 $item->compare()比较）则直接返回已经存在的那个值，否则将新的 $item 加入并返回
     */
    public function getOrAdd(IComparable $item): IComparable
    {
        $pos = $this->lastLeq($item);
        if ($pos !== -1 && $this->get($pos)->compare($item) === 0) {
            // 已存在，直接返回
            return $this->get($pos);
        }

        // 不存在，插入
        $this->insert($item, $pos + 1);
        return $item;
    }

    public function current()
    {
        return $this->data[$this->pos];
    }

    public function key()
    {
        return $this->pos;
    }

    public function rewind()
    {
        $this->pos = 0;
    }

    public function next()
    {
        ++$this->pos;
    }

    public function valid()
    {
        return isset($this->data[$this->pos]);
    }

    /**
     * 找出最后一个小于等于目标值的元素位置
     * @return int 返回符合条件的下标，如果所有元素都大于 $need，则返回 -1
     */
    private function lastLeq(IComparable $item): int
    {
        $arr = $this->data;
        $cnt = count($arr);
        $low = 0;
        $high = $cnt - 1;
        while ($low <= $high) {
            $mid = $low + (($high - $low) >> 1);
            if ($arr[$mid]->compare($item) > 0) {
                $high = $mid - 1;
            } else {
                if ($mid === $cnt - 1 || $arr[$mid + 1]->compare($item) > 0) {
                    return $mid;
                }

                $low = $mid + 1;
            }
        }

        return -1;
    }

    /**
     * 在 $pos 处插入元素
     */
    private function insert(IComparable $item, int $pos)
    {
        if (!$this->data) {
            $this->data[] = $item;
            return;
        }

        // 先将 $pos 以及后面的元素往后移动一位
        for ($i = count($this->data) - 1; $i >= $pos; $i--) {
            $this->data[$i + 1] = $this->data[$i];
        }

        // 将 $node 插入到 $pos 的位置
        $this->data[$pos] = $item;
    }
 }