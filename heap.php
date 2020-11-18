<?php

interface IComparable
{
    /**
     * 元素比较，返回：
     *  当前对象小于 $other 返回负数，相等返回 0，大于则返回正数
     */
    public function compare(IComparable $other): int;
}

/**
 * 堆
 */
 abstract class Heap
{
    /**
     * 为了方便使用，数组下标从 1 开始使用
     * $data 中存放 IComparable 对象
     */
    protected $data;
    protected $size;
    private $heapfiyed;

    public function __construct(array $data = [])
    {
        $this->data = array_merge([null], $data);// 索引 0 不保存数据
        $this->size = count($data);
        $this->heapfiyed = 0;

        if ($this->size > 1) {
            $this->heapfiy();
        }
    }

    /**
     * 堆元素个数
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * 往堆中添加元素
     * 将元素添加到末尾，并执行从下往上的堆化
     */
    public function add(IComparable $ele)
    {
        $this->size++;
        $this->data[$this->size] = $ele;// 注意：由于索引从 1 开始，要先把 size 加 1

        if ($this->size > 1) {
            $this->up($this->size);
        }
    }

    /**
     * 取出堆顶元素
     * 取出元素后，将末尾元素放到堆顶，并执行从上往下的堆化
     */
    public function pop(): ?IComparable
    {
        if (!$this->size) {
            return null;
        }

        // 先取出堆顶元素
        $ele = $this->data[1];
        if ($this->size == 1) {
            $this->size--;
            return $ele;
        }

        
        // 将最后一个元素放到堆顶
        $this->data[1] = $this->data[$this->size];
        $this->data[$this->size] = null;
        $this->size--;

        // 下沉
        if ($this->size > 1) {
            $this->down(1);
        }

        return $ele;
    }

    /**
     * 堆化（初始化）
     */
    private function heapfiy()
    {
        if ($this->heapfiyed || $this->size < 2) {
            return;
        }

        $this->heapfiyed = 1;

        // 从第一个非叶子节点开始，逐步向前执行 down 化
        $startIndex = $this->size >> 1;
        while ($startIndex > 0) {
            $this->down($startIndex);
            $startIndex--;
        }
    }

    /**
     * 向上堆化
     */
    private function up(int $index)
    {
        // 到达堆顶，退出
        if ($index == 1) {
            return;
        }

        $pIndex = $index >> 1;
        // 将 pIndex 和左右子节点比较，看是否需要执行交换
        if (($newIndex = $this->compareAndSwap($pIndex)) == $pIndex) {
            // 如果没有执行交换，说明当前位置就是合适的位置
            return;
        }

        // 对新的结点执行下沉操作
        $this->up($pIndex);
    }

    /**
     * 向下堆化
     */
    private function down(int $index)
    {
        // 不存在子节点则退出
        if (!$this->hasChild($index)) {
            return;
        }

        // 和左右子节点比较，看是否需要执行交换
        if (($newIndex = $this->compareAndSwap($index)) == $index) {
            // 如果没有执行交换，说明当前位置就是合适的位置
            return;
        }

        // 对新的结点执行下沉操作
        $this->down($newIndex);
    }

    /**
     * 是否存在子节点
     */
    private function hasChild(int $index): bool
    {
        return ($index << 1) <= $this->size;
    }

    /**
     * 对父子节点执行比较并交换
     * @return int 交换后原 parent 元素所在的新节点索引值
     */
    private function compareAndSwap(int $pIndex): int
    {
        $leftIndex = $pIndex << 1;
        $rightIndex = ($pIndex << 1) + 1;

        if (!isset($this->data[$leftIndex]) && !isset($this->data[$rightIndex])) {
            return $pIndex;
        }

        // 至此，至少存在左节点
        $targetIndex = $this->target($pIndex, $leftIndex, $rightIndex);
        if ($targetIndex === $pIndex) {
            return $pIndex;
        }

        // 交换
        $this->swap($pIndex, $targetIndex);
        return $targetIndex;
    }

    private function swap(int $i, int $j)
    {
        $tmp = $this->data[$i];
        $this->data[$i] = $this->data[$j];
        $this->data[$j] = $tmp;
    }

    /**
     * 从父节点、左节点和右节点中计算（源）父节点需要到达的新位置
     */
    abstract protected function target(int $pIndex, int $lIndex, int $rIndex): int;
}

/**
 * 大顶堆
 */
class MaxHeap extends Heap
{
    /**
     * 取最大的
     */
    protected function target(int $pIndex, int $lIndex, int $rIndex): int
    {
        $leftChild = $this->data[$lIndex] ?? null;
        $rightChild = $this->data[$rIndex] ?? null;

        $swapIndex = $rightChild === null || $rightChild->compare($leftChild) < 0 ? $lIndex : $rIndex;
        if ($this->data[$pIndex]->compare($this->data[$swapIndex]) < 0) {
            return $swapIndex;
        }

        return $pIndex;
    }
}

/**
 * 小顶堆
 */
class MinHeap extends Heap
{
    /**
     * 取最小的
     */
    protected function target(int $pIndex, int $lIndex, int $rIndex): int
    {
        $leftChild = $this->data[$lIndex] ?? null;
        $rightChild = $this->data[$rIndex] ?? null;

        $swapIndex = $rightChild === null || $rightChild->compare($leftChild) > 0 ? $lIndex : $rIndex;
        if ($this->data[$pIndex]->compare($this->data[$swapIndex]) > 0) {
            return $swapIndex;
        }

        return $pIndex;
    }
}

class IntNode implements IComparable
{
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function compare(IComparable $other): int
    {
        if (!$other instanceof IntNode) {
            throw new \Exception("invalid compare type");
        }

        if ($this->value === $other->value()) {
            return 0;
        }

        return $this->value < $other->value() ? -1 : 1;
    }

    public function value(): int
    {
        return $this->value;
    }
}

$data = [];
for ($i = 0; $i < 10; $i++) {
    $data[] = new IntNode(mt_rand(0, 100));
}

// 原始值
echo "origin data:\n";
foreach ($data as $val) {
    echo $val->value()."    ";
}
echo "\n";

// 大顶堆
$maxHeap = new MaxHeap($data);
echo "max heap,size:{$maxHeap->size()}\n";
while ($val = $maxHeap->pop()) {
    echo $val->value()."    ";
}
echo "\nfinal size:{$maxHeap->size()}\n";

// 小顶堆
$minHeap = new MinHeap($data);
echo "min heap,size:{$minHeap->size()}\n";
while ($val = $minHeap->pop()) {
    echo $val->value()."    ";
}
echo "\nfinal size:{$minHeap->size()}\n";

// 往大顶堆添加随机元素
for ($i = 0; $i < 20; $i++) {
    $val = new IntNode(mt_rand(0, 100));
    $maxHeap->add($val);
    echo "add to max heap:{$val->value()},size:{$maxHeap->size()}\n";
}

// 从大顶堆取出元素
echo "pop from max heap:\n";
while ($val = $maxHeap->pop()) {
    echo $val->value()."    ";
}
echo "\n";