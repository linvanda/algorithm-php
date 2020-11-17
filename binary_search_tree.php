<?php

/**
 * 二叉搜索树
 * 树：高度、深度、层数
 * 完全二叉树（1.除了最后一层，其它层都是满的；2.最后一层的叶子节点靠左排列）。完全二叉树适合用数组存储。且 i/2 后面（不包括 i/2）都是叶子节点
 * 非完全二叉树一般用链式存储
 * 
 * 二叉搜索树：节点的左子节点小于该结点，右子节点大于该结点。
 * 实际项目中，为了防止二叉树在不断更新过程中复杂度退化成 O(n)，一般采用平衡二叉树（如红黑树）
 * 
 * 下面的二叉搜索树的实现假设每个节点的值都不同，实际中可能会有重复的值，可以采用链表法解决重复问题（每个树节点放的是链表）
 */
class BSTree
{
    private $top;

    public function __destruct()
    {
        // TODO 删除掉所有 node 中的 parent 指针，接触双向引用
    }

    public function add($item)
    {
        if (!$this->top) {
            $this->top = new TreeNode($item);
            return;
        }

        /**
         * @var TreeNode
         */
        $curr = $this->top;
        $node = new TreeNode($item);
        while ($curr) {
            if ($curr->data() > $item) {
                // 添加的结点小于当前节点，则看其左节点
                if (!$curr->left()) {
                    // 左节点不存在，放入左节点位置，结束
                    $curr->setLeft($node);
                    $node->setParent($curr);
                    return;
                } else {
                    // 左节点存在，继续检查左节点
                    $curr = $curr->left();
                    continue;
                }
            } else {
                // 看右节点
                if (!$curr->right()) {
                    $curr->setRight($node);
                    $node->setParent($curr);
                    return;
                } else {
                    $curr = $curr->right();
                    continue;
                }
            }
        }
    }

    public function get($item): ?TreeNode
    {
        if (!$this->top) {
            return null;
        }

        /**
         * @var TreeNode
         */
        $curr = $this->top;
        while ($curr) {
            if ($curr->data() === $item) {
                return $curr;
            }

            if ($curr->data() > $item) {
                // 在左侧查
                if (!$curr->left()) {
                    return null;
                }

                $curr = $curr->left();
                continue;
            } else {
                // 在右侧查
                if (!$curr->right()) {
                    return null;
                }

                $curr = $curr->right();
                continue;
            }
        }

        return null;
    }

    /**
     * 情况：
     * 1. 待删除的元素没有子节点，则直接删除
     * 2. 待删除的元素有一个子节点，则用该子节点代替被删除的节点
     * 3. 待删除的元素有两个子节点，取其左子树的最大值（最右侧的元素）替代被删除的元素
     */
    public function remove($item)
    {
        $node = $this->get($item);
        if ($node === null) {
            return;
        }

        if ($node === $this->top) {
            return $this->removeAll();
        }

        if (!$node->left() && !$node->right()) {
            // 没有子节点，则直接删除
            $node === $node->parent()->left() ? $node->parent()->setLeft(null) : $node->parent()->setRight(null);
        }

        // 有一个子节点，让 parent 指向该子节点即可
        if (!$node->left()) {
            // 只有右节点
            $node === $node->parent()->left() ? $node->parent()->setLeft($node->right()) : $node->parent()->setRight($node->right());
        } elseif (!$node->right()) {
            // 只有左节点
            $node === $node->parent()->left() ? $node->parent()->setLeft($node->left()) : $node->parent()->setRight($node->left());
        }

        // 有两个子节点，取其左子树的最大值（最右侧的元素）替代被删除的元素
        $rightMaxNode = $this->getMaxNode($node->left());
        $this->switchNode($rightMaxNode, $node);
    }

    /**
     * 注意：其中 $needNode 是叶节点
     */
    private function switchNode(TreeNode $needNode, TreeNode $replacedNode)
    {
        // needNode 和父节点脱离关系
        $needNode->parent()->left() === $needNode ? $needNode->parent()->setLeft(null) : $needNode->parent()->setRight(null);
        // needNode 的 parent 指向 replacedNode 的
        $needNode->setParent($replacedNode->parent());
        
    }

    private function getMaxNode(TreeNode $parentNode)
    {
        if (!$parentNode->right()) {
            return $parentNode;
        }

        return $this->getMaxNode($parentNode->right());
    }

    public function removeAll()
    {

    }

    /**
     * 前序遍历
     */
    public function preList(): array
    {

    }

    /**
     * 中序遍历
     */
    public function inList(): array
    {

    }

    /**
     * 后序遍历
     */
    public function postList(): array
    {

    }
}

class TreeNode
{
    private $data;
    private $left;
    private $right;
    private $parent;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function data()
    {
        return $this->data;
    }

    public function setLeft(TreeNode $node = null)
    {
        $this->left = $node;
    }

    public function setRight(TreeNode $node = null)
    {
        $this->right = $node;
    }

    public function setParent(TreeNode $node)
    {
        $this->parent = $node;
    }

    public function left(): ?TreeNode
    {
        return $this->left;
    }

    public function right(): ?TreeNode
    {
        return $this->right;
    }

    public function parent(): ?TreeNode
    {
        return $this->parent;
    }
}
