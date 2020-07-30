<?php

/**
 * 双向链表
 */
class DoubleLink
{
    private $head;
    private $tail;

    public function __construct()
    {
        // 创建空头结点（Guard）
        $this->head = new Node(null);
        $this->tail = $this->head;
    }

    /**
     * 链表尾部追加元素
     */
    public function append($content)
    {
        $node = new Node($content);
        $this->tail->setNext($node);
        $node->setPrev($this->tail);
        $this->tail = $node;
    }

    /**
     * 在链表开头插入元素
     */
    public function unshift($content)
    {
        $this->insertAfter($this->head, $content);
    }

    /**
     * 在某结点后面插入元素
     */
    public function insertAfter(Node $beforeNode, $content)
    {
        if ($beforeNode->next() === null) {
            return $this->append($content);
        }

        $node = new Node($content);

        // 先建立当前元素和 next 的关系
        // 将该元素的 next 指向 beforeNode 的 next
        $node->setNext($beforeNode->next());
        // 将 next 的 prev 指向该元素
        $node->next()->setPrev($node);

        // 再建立当前元素和 beforeNode 的关系
        // 将 beforeNode 的 next 指向该元素
        $beforeNode->setNext($node);
        // 将当前元素的 prev 指向 beforeNode
        $node->setPrev($beforeNode);
    }

    /**
     * 查找元素，返回对应的结点
     */
    public function find($content): ?Node
    {
        $p = $this->head->next();
        while ($p !== null) {
            if ($p->content() === $content) {
                return $p;
            }

            $p = $p->next();
        }

        return null;
    }

    /**
     * 从链表中删除元素
     */
    public function remove($content)
    {
        if (!$node = $this->find($content)) {
            return;
        }

        $this->removeNode($node);
    }

    /**
     * 从链表中删除结点
     */
    public function removeNode(Node $node)
    {
        if (!$node->prev()) {
            return;
        }

        // 将该结点的前置结点指向该结点的后置结点
        $node->prev()->setNext($node->next());
        if ($node->next()) {
            // 如果有后置结点，则将后直接点的 pre 指向该结点的前置结点
            // 至此，没有指针指向该节点，该节点会被 gc 回收
            $node->next()->setPrev($node->prev());
        } else {
            // 删除的是尾部节点，需要修改 tail
            $this->tail = $node->prev();
        }
    }

    public function isEmpty(): bool
    {
        return $this->head->next() === null;
    }
}

class Node
{
    private $content;
    private $prev;
    private $next;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function content()
    {
        return $this->content;
    }

    public function setPrev(Node $node = null)
    {
        $this->prev = $node;
    }

    public function setNext(Node $node = null)
    {
        $this->next = $node;
    }

    public function prev(): ?Node
    {
        return $this->prev;
    }

    public function next(): ?Node
    {
        return $this->next;
    }
}
