<?php

/**
 * 使用链表实现栈
 */
class Stack
{
    private $head;

    public function push($content)
    {
        if (!$this->head) {
            $this->head = new Node($content);
        } else {
            $node = new Node($content);
            $node->setNext($this->head);
            $this->head = $node;
        }
    }

    public function pop()
    {
        if ($this->isEmpty()) {
            throw new \Exception("栈为空");
        }

        $node = $this->head;
        $this->head = $node->next();

        return $node->content();
    }

    public function isEmpty()
    {
        return $this->head === null;
    }

    /**
     * 返回栈顶元素（不出栈）
     */
    public function top()
    {
        return $this->head ? $this->head->content() : null;
    }
}

/**
 * 链表节点
 */
class Node
{
    private $content;
    private $next;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function setNext(Node $node = null)
    {
        $this->next = $node;
    }

    public function next()
    {
        return $this->next;
    }

    public function content()
    {
        return $this->content;
    }
}
