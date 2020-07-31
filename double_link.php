<?php

/**
 * 双向链表
 */
class DoubleLink
{
    private $head;
    private $tail;
    private $len;

    public function __construct()
    {
        // 创建空头结点（Guard）
        $this->head = new Node(null);
        $this->tail = $this->head;
        $this->len = 0;
    }

    /**
     * 双向链表的结点之间存在循环依赖，需要手动清理结点
     */
    public function __destruct()
    {
        $this->removeAll();
    }

    public function first(): ?Node
    {
        return $this->isEmpty() ? null : $this->head->next();
    }

    public function last(): ?Node
    {
        return $this->isEmpty() ? null : $this->tail;
    }

    /**
     * 链表尾部追加元素
     */
    public function append($content)
    {
        $this->appendNode(new Node($content));
    }

    public function appendNode(Node $node)
    {
        $this->tail->setNext($node);
        $node->setPrev($this->tail);
        // 将 next 指针置空，防止指向其他地方了
        $node->setNext(null);
        $this->tail = $node;
        $this->len++;
    }

    /**
     * 在链表开头插入元素
     */
    public function unshift($content)
    {
        $this->insertAfterNode($this->head, $content);
    }

    public function replaceNode(Node $old, Node $new)
    {
        if ($new === null) {
            return $this->removeNode($old);
        }

        $new->setPrev($old->prev());
        $old->prev()->setNext($new);

        if ($old->next()) {
            $new->setNext($old->next());
            $old->next()->setPrev($new);
        } else {
            $this->tail = $new;
        }
    }

    public function insertAfter($beforeContent, $content)
    {
        if (!$beforeNode = $this->find($beforeContent)) {
            return;
        }

        $this->insertAfterNode($beforeNode, new Node($content));
    }

    /**
     * 在某结点后面插入元素
     */
    public function insertAfterNode(Node $beforeNode, Node $node)
    {
        if ($beforeNode->next() === null) {
            return $this->appendNode($node);
        }

        // 先建立当前元素和 next 的关系
        // 将该元素的 next 指向 beforeNode 的 next
        $node->setNext($beforeNode->next());
        // 将 next 的 prev 指向该元素
        $node->next()->setPrev($node);

        // 再建立当前元素和 prev 的关系
        // 将 beforeNode 的 next 指向该元素
        $beforeNode->setNext($node);
        // 将当前元素的 prev 指向 beforeNode
        $node->setPrev($beforeNode);

        $this->len++;
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
     * 删除所有结点
     */
    public function removeAll()
    {
        if ($this->isEmpty()) {
            return;
        }

        // 先切断所有的 prev 指针，然后切断结点和 head 的关联，这样所有的结点都会被 gc 回收
        while ($this->tail !== $this->head) {
            $this->tail = $this->tail->prev();
            $this->tail->next()->setPrev(null);
        }

        $this->head->setNext(null);
        $this->len = 0;
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

        $this->len--;
    }

    public function isEmpty(): bool
    {
        return $this->len === 0;
    }

    public function length(): int
    {
        return $this->len;
    }
}

/**
 * 链表节点
 */
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
