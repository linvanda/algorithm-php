<?php

include_once './hash_table.php';

/**
 * 通过散列表和链表实现 LRU 缓存
 * 目前的散列表是用双向链表，可以把散列表改成用单链表，然后把双向链表功能留给 Cache 用
 */
class Cache
{
    private $size;
    private $hashTable;
    private $doubleLink;

    /**
     * @param int $size 缓存大小，0 表示不限制，必须是 2 的次方数
     */
    public function __construct($size = 0)
    {
        $this->size = $size >= 0 && $size & ($size - 1) === 0 ? $size : 0;
    }
    public function set($key, $val)
    {
        // TODO
    }

    public function get($key)
    {
        // TODO
    }
}
