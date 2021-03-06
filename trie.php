<?php

include './ordered_array.php';

/**
 * trie 树（字典树）
 * trie 树属于 n 叉树，这里我们用有序数组存储子节点（也可以用其他数据结构如跳表）
 * 此处的实现假设我们要处理的字符全部是英文字符
 */
 class TrieTree
 {
    private $root;

    public function __construct()
    {
        // 根节点不包含任何字符
        $this->root = new Node('');
    }

     /**
      * 根据提供的单词集合构建 trie 树
      */
    public static function build(array $wordsArr): TrieTree
    {
        $tree = new TrieTree();
        foreach ($wordsArr as $word) {
            $tree->addWord($word);
        }

        return $tree;
    }

    /**
     * 往树添加一个单词
     */
    public function addWord(string $word)
    {
        $word = trim($word);
        if (!$word) {
            return;
        }

        $len = strlen($word);
        $p = $this->root;// 当前正在操作的结点，从根节点开始
        for ($i = 0; $i < $len; $i++) {
            $p = $p->getOrAddChild($word[$i]);// 获取或者创建字符结点，并将指针指向该结点
        }

        // 单词添加完毕，将最后一个字符的词频加 1
        $p->incrFreq();
    }

    /**
     * 根据前缀搜索相关的单词集合
     */
    public function preSearch(string $wordPrefix): array
    {
        $wordPrefix = trim($wordPrefix);
        if (!$wordPrefix) {
            return [];
        }

        $len = strlen($wordPrefix);
        $p = $this->root;
        for ($i = 0; $i < $len; $i++) {
            if (($p = $p->getChild($wordPrefix[$i])) === null) {
                // 前缀尚未遍历完毕就出现不匹配，则没有任何单词能够匹配
                return [];
            }
        }

        // 从 $p 开始，遍历返回所有的单词（不包括 $p）
        $words = [];
        $this->getAllWords($p, '', $words);

        if (!$words) {
            return [];
        }

        // 加上前缀
        $pre = substr($wordPrefix, 0 , strlen($wordPrefix) - 1);
        return array_map(function ($word) use ($pre) {
            return $pre . $word;
        }, $words);
    }

    /**
     * 查看一个单词的词频
     */
    public function getFrequency(string $word): int
    {
        $word = trim($word);
        if (!$word) {
            return 0;
        }

        $len = strlen($word);
        $p = $this->root;
        for ($i = 0; $i < $len; $i++) {
            if (($p = $p->getChild($word[$i])) === null) {
                // 前缀尚未遍历完毕就出现不匹配，则没有任何单词能够匹配
                return 0;
            }
        }

        return $p->freq();
    }

    /**
     * @param Node $node 当前处理的结点
     * @param string $prefix 到当前节点时的前缀子串
     * @param array $arr 放单词的数组
     */
    private function getAllWords(Node $node, string $prefix, &$arr)
    {
        // 将当前字符拼接到前缀子串中
        $prefix .= $node->char();

        // 如果当前节点构成单词，则放入到数组中
        if ($node->isWord()) {
            $arr[] = $prefix;
        }

        // 有子节点，则继续处理子节点
        // 深度优先遍历
        $children = $node->children();
        foreach ($children as $childNode) {
            $this->getAllWords($childNode, $prefix, $arr);
        }
    }
 }

 /**
  * 节点
  */ 
 class Node implements IComparable
 {
     private $char;
     private $wordFreq;// 单词出现的频率（次数）
     private $children;

    public function __construct(string $char)
    {
        $this->char = $char;
        $this->wordFreq = 0;
        $this->children = new OrderedArray();
    }

    public function compare(IComparable $other): int
    {
        if (!$other instanceof Node) {
            throw new \Exception("invalid op");
        }

        $otherChar = $other->char();
        if ($otherChar === $this->char) {
            return 0;
        }

        return $this->char > $otherChar ? 1 : -1;
    }

    public function char(): string
    {
        return $this->char;
    }

    /**
     *  从根节点到此处是否构成单词
     */
    public function isWord(): bool
    {
        return $this->wordFreq > 0;
    }

    /**
     * 增加词频
     */
    public function incrFreq()
    {
        $this->wordFreq++;
    }

    /**
     * 词频
     */
    public function freq(): int
    {
        return $this->wordFreq;
    }

    public function children(): OrderedArray
    {
        return $this->children;
    }

    /**
     * 如果 $char 在该结点的子节点中，则直接返回该结点，否则创建新节点插入到子节点列表中并返回该结点（的引用）
     */
    public function getOrAddChild(string $char): Node
    {
        return $this->children->getOrAdd(new Node($char));
    }

    /**
     * 获取子节点中字符为 $char 的结点，如果不存在则返回 null
     */
    public function getChild(string $char): ?Node
    {
        if (($index = $this->children->search(new Node($char))) !== -1) {
            return $this->children->get($index);
        }

        return null;
    }
 }

/**
 * 测试
 */
$words = [
    'word', 'work', 'would', 'wonderful', 'yan', 'yamy', 'free', 'fresh', 'fruit', 'from', 'front', 'frontend', 'and', 
    'as', 'all', 'also', 'ability', 'abs', 'able', 'abandon', 'work', 'wonderful', 'yamy', 'free', 'fresh',
    'as', 'all', 'also','as', 'all', 'also','as', 'all', 'also', 'yanny', 'yannor'
];

echo "origin data:".print_r($words, true)."\n\n";
$tree = TrieTree::build($words);
echo "freq:'all':" . $tree->getFrequency('all');
echo "\n";
echo "freq:'work':" . $tree->getFrequency('work');
echo "\n";
echo "freq:'nothing':" . $tree->getFrequency('nothing');
echo "\n";
echo "freq:'yan':" . $tree->getFrequency('yan');
echo "\n";
echo "pre search:'wo':" . print_r($tree->preSearch('wo'), true) . "\n";
echo "pre search:'ab':" . print_r($tree->preSearch('ab'), true) . "\n";
echo "pre search:'work':" . print_r($tree->preSearch('work'), true) . "\n";
echo "pre search:'workman':" . print_r($tree->preSearch('workman'), true) . "\n";
