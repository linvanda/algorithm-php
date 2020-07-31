<?php

include './double_link.php';

/**
 * 跳表索引节点
 */
class SkipListNode extends Node
{
    private $downNode;
    private $origNode;
    private $level;

    /**
     * @param Node $downNode 下方的节点
     * @param Node $origNode 原始链表中的节点
     * @param int $level 所在的索引层，从 0 开始
     */
    public function __construct(Node $downNode, Node $origNode, int $level)
    {
        $this->downNode = $downNode;
        $this->origNode = $origNode;
        $this->level = $level;
    }

    public function content()
    {
        return $this->origNode->content();
    }

    public function origNode(): Node
    {
        return $this->origNode;
    }

    public function downNode(): Node
    {
        return $this->downNode;
    }

    public function level(): int
    {
        return $this->level;
    }
}

/**
 * 跳表
 * 跳表让链表拥有二分查找的性能（O(logn)，同时支持范围查找
 * 实现：将链表中的每 m（一般取 2 - 5）个元素取第一个元素向上一层创建索引（用这些元素建立的新链表），直到最上层索引元素个数 <= m
 * n 个元素的链表，每 m 个取一个建立索引（步长 m），则需要建立log↓mn层索引（包括原始数据本身）
 * 因为是对数关系，层级增长会非常缓慢，m 一般取 2 - 3 即可
 * 优化：可让跳表继承基础的链表，这样外界需要链表时可以根据实际情况使用跳表来提升查询性能
 */
class SkipList
{
    private const DEFAULT_STEP = 3;

    // 原始链表
    private $list;
    // 索引
    private $indexLists;
    // 索引层数（不包括原始数据自身）
    private $level;
    // 跳表步长
    private $step;
    /**
     * 跳表头元素，从此处开始查找元素
     * @var Node
     */
    private $head;

    public function __construct(DoubleLink $doubleLink = null, $step = 3)
    {
        $this->list = $doubleLink ?: new DoubleLink();
        $this->indexLists = [];
        $this->level = 0;
        $this->step = $step && $step < 10 ? $step : self::DEFAULT_STEP;

        $this->buildSkipList($this->list);
    }

    /**
     * 查找元素
     * 没找到返回 Null，否则返回 content
     */
    public function find($content)
    {
        $current = $this->head;

        // 如果第一个就大于目标，则直接返回不存在
        if (!$current || $current->content() > $content) {
            return null;
        }

        // 从最上层索引链表开始找，逐渐下沉
        while ($current) {
            // 找到目标
            if ($current->content() === $content) {
                return $current instanceof SkipListNode ? $current->origNode()->content() : $current->content();
            }

            // 当前小于目标值，下一个元素大于目标值（或者不存在）
            if ($current->content() < $content && (!$current->next() || $current->next()->content() > $content)) {
                if ($current instanceof SkipListNode) {
                    // 当前节点是索引节点，则跳到下一层查找
                    $current = $current->downNode();
                    continue;
                } else {
                    // 已经是最后一层，没有找到
                    return null;
                }
            }

            // 继续往后面查找
            $current = $current->next();
        }

        return null;
    }

    /**
     * 根据范围查找元素
     * 没查到则返回空数组，否则返回相关元素数组
     * 查找思路：先找第一个大于等于 startContent 的，找到后记录下其原始节点的位置，然后查找最后一个小于等于 endContent 的，记录下位置，
     * 然后遍历这两个节点以及之间的节点，就是要找的范围
     */
    public function findRange($startContent, $endContent): array
    {
        //TODO
    }

    /**
     * 删除元素
     */
    public function remove($content)
    {
        //TODO
    }

    /**
     * 添加元素
     */
    public function append($content)
    {
        //TODO
    }

    /**
     * 建立跳表
     */
    private function buildSkipList(DoubleLink $list)
    {
        if ($list->length() <= $this->step) {
            $this->head = $list->first();
            return;
        }

        // 建立索引链表。索引链表的 content 指向源链表节点
        $indexList = new DoubleLink();
        $current = $list->first();
        while ($current) {
            // 注意：将 current（当前源节点的引用）作为 content
            $indexList->appendNode(new SkipListNode($current, $this->level === 0 ? $current : $current->origNode(), $this->level));

            // 跳到后面第 step 个节点
            $i = $this->step;
            while ($i-- > 0) {
                if (!$current = $current->next()) {
                    break;
                }
            }
        }

        // 将第 level 层索引链表挂进去，并将 level 加 1
        $this->indexLists[$this->level++] = $indexList;

        // 继续对 indexList 建立索引
        $this->buildSkipList($indexList);
    }
}

$start = time();
$dlink = new DoubleLink();
for ($i = 0; $i < 10000000; $i++) {
    $dlink->append($i);
}
echo "created data:" . (time() - $start). "\n";
$skList = new SkipList($dlink, 6);
echo "new list:" . (time() - $start). "\n";
$result = $skList->find(19999990);
echo "find:" . (time() - $start). "\n";

if (!$result) {
    echo "no result\n";
} else {
    echo "got it:" . $result->content() . ",prev:" . $result->prev()->content() . ",next:" . $result->next()->content()."\n";
}

// $current = $dlink->first();
// while ($current) {
//     if ($current->content() === 19999990) {
//         echo "got it\n";
//         break;;
//     }
//     $current = $current->next();
// }
// echo "no\n";
// echo "find:" . (time() - $start). "\n";