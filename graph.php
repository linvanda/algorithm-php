<?php

include "./ordered_array.php";

/**
 * 图
 * 这里实现带权重的有向图
 * 使用邻接表存储，其中邻接表通过有序数组实现，以支持快速查找
 * $arr 的下标 $i 表示第 $i 个顶点（从 0 开始），存储的值是有序数组，表示从顶点 $i 引出的边（出度）列表
 */
class Graph
{
    /**
     * 顶点数
     */
    private $v;
    private $data;

    public function __construct(int $v)
    {
        $this->v = $v;
        // 初始化 $data
        for ($i = 0; $i < $v; $i++) {
            $this->data[$i] = new OrderedArray();
        }
    }

    /**
     * 添加一条从 $src -> $dst 的边，权重为 $weight
     */
    public function addEdge(int $src, int $dst, int $weight = 0)
    {
        if ($src < 0 || $src >= $this->v) {
            throw new \Exception("invalid src index:$src");
        }

        if ($dst < 0 || $dst >= $this->v) {
            throw new \Exception("invalid dst index:$dst");
        }

        // 创建边加入
        $this->data[$src]->getOrAdd(new Edge($src, $dst, $weight));
    }

    /**
     * 广度优先搜索
     * 使用队列实现
     * 在无权图中，得到的就是 $src 到 $dst 的最短路径
     * @return array 按照广度优先搜索得到的顶点列表（int 型）
     */
    public function BFSearch(int $src, int $dst): array
    {
        if (!isset($this->data[$src])) {
            return [];
        }

        $queue = new SplQueue();
        $visited = [];// 节点是否已经访问过
        $pathMap = [];// 来源跟踪，k => v 表示 k 是从 v 顶点访问来的，如果没有来源顶点则 v = -1
        
        // 将 $src 入列
        $queue->enqueue($src);
        $pathMap[$src] = -1;

        while (!$queue->isEmpty()) {
            $theDst = $queue->dequeue();

            // 处理过了则不处理了
            if (isset($visited[$theDst])) {
                continue;
            }

            $visited[$theDst] = true;

            // 已找到，跳出
            if ($theDst === $dst) {
                break;
            }

            // 将当前节点的相邻节点加到队列中
            foreach ($this->data[$theDst] as $edge) {
                $nDst = $edge->dst();
                $queue->enqueue($nDst);
                $pathMap[$nDst] = $theDst;
            }
        }

        // 如果 $dst 不在 $pathMap 中，则说明没找到
        if (!isset($pathMap[$dst])) {
            return [];
        }

        // 从 $dst 开始通过 $pathMap 往回回溯
        $paths = [];
        $currV = $dst;
        while (true) {
            // 防止环
            if (in_array($currV, $paths)) {
                throw new \Exception("ring map occurs");
            }

            if ($currV === -1 || $currV === null) {
                break;
            }

            $paths[] = $currV;
            $currV = $pathMap[$currV] ?? null;
        }

        return array_reverse($paths);
    }

    /**
     * 深度优先搜索
     * @return array 按照深度优先搜索顺序得到的顶点列表（int 型）
     */
    public function DFSearch(): array
    {
        return [];
    }

    /**
     * 拓扑排序
     * @return array 排序后的顶点列表（int 型）
     */
    public function topoSort(): array
    {
        return [];
    }

    /**
     * dijkstra算法求顶点 $src 到 $dst 的最短路径
     * @return array 路径经过的顶点列表（int）
     */
    public function dijkstra(int $src, int $dst): array
    {
        return [];
    }
}

/**
 * 边
 */
class Edge implements IComparable
{
    private $src;
    private $dst;
    private $weight;

    /**
     * @param int $src 源顶点
     * @param int $dst 目标顶点
     * @param int $weight 权重
     */
    public function __construct(int $src, int $dst, int $weight)
    {
        $this->src = $src;
        $this->dst = $dst;
        $this->weight = $weight;
    }

    public function src(): int
    {
        return $this->src;
    }

    public function dst(): int
    {
        return $this->dst;
    }

    public function weight(): int
    {
        return $this->weight;
    }

    /**
     * 边的比较：此方法是给邻接表的有序数组用的，只需要比较 dst 即可
     * 如果不相等，则
     */
    public function compare(IComparable $other): int
    {
        if (!$other instanceof Edge) {
            throw new \Exception("invalid op");
        }

        if ($other->dst() === $this->dst) {
            return 0;
        }

        return $this->dst > $other->dst() ? 1 : -1;
    }
}

$graph = new Graph(5);
// 5 个顶点测试
$v = [
    0 => [1, 1, 0, 1, 1],
    1 => [0, 1, 1, 0, 0],
    2 => [0, 0, 1, 0, 1],
    3 => [0, 0, 0, 1, 0],
    4 => [0, 0, 0, 1, 1],
];

foreach ($v as $i => $k) {
    foreach ($k as $j => $l) {
        if ($i === $j || $l === 0) {
            continue;
        }

        $graph->addEdge($i, $j);
    }
}

$arr = $graph->BFSearch(2, 3);
echo "BFS：2 -> 3：\n";
var_export($arr);
echo "\n";

$arr = $graph->BFSearch(1, 3);
echo "BFS：1 -> 3：\n";
var_export($arr);
echo "\n";

$arr = $graph->BFSearch(2, 1);
echo "BFS：2 -> 1：\n";
var_export($arr);
echo "\n";