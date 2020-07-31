<?php

include_once './double_link.php';

/**
 * 散列表
 * 简单起见，此处只支持字符串或整型作为 Key
 * 内部使用数组做散列映射，用链表解决 hash 冲突
 * 每次 set 元素时，都会检查是否需要扩容
 * 扩容后，旧表仍然保留，后面每次 set 都会从旧表搬迁部分数据到新表，防止一次搬迁造成卡顿
 */
class HashTable
{
    // 数组
    public $table;
    // 数组扩容时尚未完全废弃的老 Table
    public $oldTable;
    // 旧表迁移指针
    private $oldPoint;
    // 数组容量
    public $capacity;
    // 数组使用量
    public $used;
    // 元素总数
    private $count;
    // 装载因子因，超过该值将触发数组扩容
    private $threshold;

    /**
     * $capacity int 必须是 2 的次方数
     */
    public function __construct(int $capacity = 16, float $trheshold = 0.75)
    {
        $this->threshold = $trheshold > 0.5 && $trheshold <= 1 ? $trheshold : 0.75;
        // 注意：count 只会初始化的时候设置为 0，创建新 table 的时候不会改变 count 的值
        $this->count = $this->oldPoint = 0;
        $this->newTable($capacity);
    }

    /**
     * 添加或修改
     */
    public function set($key, $val)
    {
        if ($val === null) {
            return $this->remove($key);
        }

        // 首先看看是否需要扩容
        $this->tryToExtend($key);
        $this->addToTable(new HashNode($key, $val));
    }

    /**
     * 查询要先从老的 Table 查（如果有的话），再从新的查
     */
    public function get($key)
    {
        $content = null;
        if ($this->oldTable) {
            // 在旧表找
            $content = $this->find($this->index($key, $this->capacity >> 1), $this->oldTable, $key);
        }

        if ($content === null) {
            // 在新表找
            $content = $this->find($this->index($key), $this->table, $key);
        }

        return $content;
    }

    /**
     * 先从旧表删除，再从新表删除
     */
    public function remove($key)
    {
        // 试图从旧表删除
        if ($this->oldTable && $this->delete($this->index($key, $this->capacity >> 1), $this->oldTable, $key)) {
            return;
        }

        // 从新表删除
        $this->delete($this->index($key, $this->capacity), $this->table, $key);
    }

    public function count(): int
    {
        return $this->count;
    }

    private function delete($index, $table, $key): bool
    {
        $node = $table[$index] ?? null;
        if ($node === null) {
            return false;
        }

        // 普通节点
        if ($node instanceof HashNode) {
            if ($node->key() === $key) {
                unset($table[$index]);
                $this->count--;

                if ($table === $this->table) {
                    // 如果是从新表删除，需要扣减 used
                    $this->used--;
                }

                return true;
            }
            
            return false;
        }

        // 链表
        $current = $node->first();
        while ($current) {
            if ($current->key() === $key) {
                $node->removeNode($current);
                $this->count--;

                if ($node->isEmpty()) {
                    // 链表空了，删除该链表
                    unset($table[$index]);
                    if ($table === $this->table) {
                        // 如果是从新表删除，需要扣减 used
                        $this->used--;
                    }
                }

                return true;
            }
            $current = $current->next();
        }

        return false;
    }

    private function find($index, $table, $key)
    {
        $node = $table[$index] ?? null;
        if ($node === null) {
            return null;
        }

        if ($node instanceof HashNode) {
            return $node->key() === $key ? $node->content() : null;
        }

        // 链表
        $current = $node->first();
        while ($current) {
            if ($current->key() === $key) {
                return $current->content();
            }
            $current = $current->next();
        }

        return null;
    }

    /**
     * 创建新 hash 表
     */
    private function newTable(int $capacity)
    {
        // 注意 & 运算比比较运算优先级低
        $this->capacity = $capacity > 0 && ($capacity & ($capacity - 1)) === 0 ? $capacity : 16;
        $this->used = 0;
        $this->table = new SplFixedArray($this->capacity);
    }

    private function tryToExtend($key)
    {
        if ($this->oldTable) {
            // 如果存在 oldTable，则从 oldTable 搬迁部分数据过去
            // 由于数据搬迁时，一次插入会搬迁两条数据，因而在老数据搬迁完毕之前新数组不会超过载荷
            $this->tryToMove($key);
            return;
        }

        if ($this->used / $this->capacity < $this->threshold) {
            return;
        }

        // 创建一个容量是原来两倍的数组
        $newCapacity = $this->capacity << 1;
        if ($newCapacity < $this->capacity) {
            // 超过整型范围
            throw new \Exception("too many element");
        }

        $this->oldTable = $this->table;
        $this->newTable($newCapacity);
        // 迁移部分数据
        $this->tryToMove($key);
    }

    /**
     * 一次最多迁移两个元素
     * $key 对应的位置如果有元素，则迁移到新表（这样在添加元素的时候就不用考虑去旧表查有无该元素了）
     */
    private function tryToMove($key = null)
    {
        if (!$this->oldTable || $this->oldPoint >= count($this->oldTable)) {
            return;
        }

        $cnt = 0;
        for ($i = $this->oldPoint; $i < count($this->oldTable); $i++) {
            // 将 oldPoint 指向下一个要处理的元素
            $this->oldPoint++;

            if (!$this->moveToNewTable($i)) {
                continue;
            }

            if ($cnt++ > 1) {
                break;
            }
        }

        // 如果 key 所在的位置有元素，则一并迁移
        $idx = $this->index($key, $this->capacity >> 1);
        if ($key && $idx >= $this->oldPoint) {
            $this->moveToNewTable($idx);
        }

        // 如果全部迁移完了，则删除旧表
        if ($this->oldPoint >= count($this->oldTable)) {
            $this->oldTable = null;
            $this->oldPoint = 0;
        }
    }

    /**
     * 从新表迁移到旧表
     */
    private function moveToNewTable($index): bool
    {
        $node = $this->oldTable[$index] ?? null;
        if ($node === null) {
            return false;
        }

        if ($node instanceof HashNode) {
            // 普通的 hash 节点，直接迁移
            $this->addToTable($node, true);
        } elseif ($node instanceof DoubleLink) {
            // 链表，遍历迁移（里面的每个元素的 hash 值不一定相同，因而在新表的位置不一定相同）
            $current = $node->last();
            while (!$node->isEmpty()) {
                // 注意下面的操作顺序，在两个链表之间转移元素，需要谨慎，防止指针错误指向
                $currNode = $current;
                // 指针前移
                $current = $current->prev();
                // 从当前链表中删除该结点
                $node->removeNode($currNode);
                // 添加到新表中
                $this->addToTable($currNode, true);
            }
        }

        unset($this->oldTable[$index]);
        return true;
    }

    /**
     * 将节点添加到 hash 表中
     * 我们假设 hash 冲突是效率概率事件，因而优先直接存储 hash 节点，当遇到冲突时才转换成双向链表
     * @param $node HashNode 待添加的结点
     * @param $isMove bool 是否从旧表迁移过来的
     */
    private function addToTable(HashNode $node, $isMove = false)
    {
        $idx = $this->index($node->key());
        $currentEle = $this->table[$idx];
        if ($currentEle === null) {
            // 当前位置没有元素，直接插入
            $this->table[$idx] = $node;
            $this->used++;
            !$isMove && $this->count++;
        } elseif ($currentEle instanceof DoubleLink) {
            // 当前位置是双向链表
            $curr = $currentEle->first();
            $newKey = $node->key();
            while ($curr) {
                if ($newKey === $curr->key()) {
                    // 替换
                    $currentEle->replaceNode($curr, $node);
                    return;
                }
                $curr = $curr->next();
            }

            // 追加
            $currentEle->appendNode($node);
            !$isMove && $this->count++;
        } else {
            // 当前位置是一个普通元素节点
            if ($node->key() === $currentEle->key()) {
                // key 相同， 替换掉
                $this->table[$idx] = $node;
            } else {
                // 转换为双向链表
                $dlink = new DoubleLink();
                $dlink->appendNode($currentEle);
                $dlink->appendNode($node);
                $this->table[$idx] = $dlink;
                !$isMove && $this->count++;
            }
        }
    }

    /**
     * 求数组下标
     * 由于 capacity 是 2 的次方数，对其求模就等于和其减 1 的值求与运算
     */
    private function index($key, $capacity = null): int
    {
        return $this->hash($key) & (($capacity ?: $this->capacity) - 1);
    }

    /**
     * 只支持数字或者字符串
     * 注意：PHP 的 crc32 在 32 位系统上可能会得到负数，此处用 sprintf 格式化为正数
     */
    private function hash($key): int
    {
        if (!is_numeric($key) && !is_string($key)) {
            throw new \Exception("invalid key");
        }

        return sprintf('%u', crc32($key));
    }
}

class HashNode extends Node
{
    private $key;

    public function __construct($key, $content)
    {
        $this->key = $key;
        parent::__construct($content);
    }

    public function key()
    {
        return $this->key;
    }
}

// --- test ---
// $ht = new HashTable();
// $ht->set("add", 34);

// for ($i = 0; $i < 12290; $i++) {
//     $ht->set("k{$i}", $i * 3);
// }

// for ($i = 0; $i < 12180; $i++) {
//     $ht->remove("k{$i}");
// }
// echo "used:".$ht->used;

// $ht->remove('add');
// echo "total:".$ht->count()."\n";
// echo "cap:{$ht->capacity}\n";
// echo "get:" . $ht->get("add")."\n";
// $ht->set("add", 100);
// echo "get again:" . $ht->get("add")."\n";

// $cnt = 0;
// $max = 0;
// foreach ($ht->table as $item) {
//     if ($item instanceof DoubleLink) {
//         $cnt++;
//         $max = max($max, $item->length());
//     }
// }
// echo "cnt:$cnt,max:$max\n";

// one:k2253two:k12283, one:k2255two:k12285
// echo (sprintf('%u', crc32('k2253'))%16384).','.(sprintf('%u', crc32('k12283'))%16384);
