<?php

/**
 * 通过数组实现环形队列
 */
class RingQueue
{
    private $size;
    private $queue;
    // 头指针指向队列第一个元素
    private $head;
    // 为了方便，尾指针指向最后一个元素后面的空位置
    private $tail;
    private $count;

    public function __construct($size)
    {
        $this->size = $size;
        $this->queue = new SplFixedArray($size);
        // 初始化时头尾指针都指向 0
        $this->head = $this->tail = $this->count = 0;
    }

    /**
     * 入列
     */
    public function enqueue($object)
    {
        if ($this->isFull()) {
            throw new \Exception("队列满了");
        }

        $this->queue[$this->tail] = $object;
        $this->count++;
        $this->tail = (++$this->tail) % $this->size;
    }

    public function dequeue()
    {
        if ($this->isEmpty()) {
            throw new \Exception("队列空了");
        }

        $res = $this->queue[$this->head];
        $this->count--;
        $this->head = (++$this->head) % $this->size;
        return $res;
    }

    public function count()
    {
        return $this->count;
    }

    /**
     * 当头尾指针重叠时，队列为空
     */
    public function isEmpty()
    {
        return $this->head === $this->tail;
    }

    /**
     * 当尾指针加 1 然后对 size 取模等于头指针时，队列为满（通过取模实现环形周期）
     */
    public function isFull()
    {
        return ($this->tail + 1) % $this->size === $this->head;
    }
}
