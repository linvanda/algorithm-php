<?php

include './stack.php';

/**
 * 通过栈实现计算器
 * 支持的运算：+、-、*、/、%、()
 * 实现方式：
 *  使用两个栈（一个数据栈，一个操作栈），当读取到数据时压入数据栈，读取到操作符时，判断当前操作符
 *  和栈顶操作符的优先级，如果当前操作符优先级更高，说明要先执行该操作符，因而将该操作符入栈，继续往后面查找；
 *  如果当前操作符优先级比栈顶操作符的低（或者相等），说明需要先执行前面的运算得出运算结果并入列到数据栈中；
 *  每一对括号作为子分组单独运算，然后返回给上层，是一个树形递归的过程
 */
class Calculator
{
    // 运算符优先级
    private $priority = ['+' => 10, '-' => 10, '*' => 20, '/' => 20, '%' => 20];

    public function operate($exp)
    {
        $exp = str_replace(' ', '', $exp);

        // 创建数据栈和操作栈
        $dataStack = new Stack();
        $opStack = new Stack();
        
        $start = 0;// 数字截取：开始下标
        $len = 0;// 数字截取：长度
        $opArr = array_keys($this->priority);
        for ($i = 0; $i < strlen($exp); $i++) {
            $char = $exp[$i];

            // 当前字符是操作符
            if (in_array($char, $opArr)) {
                // 第一个和最后一个不能是操作符
                if ($i === strlen($exp) || $i === 0) {
                    throw new \Exception("非法的表达式");
                }

                // 先将数字压栈
                if ($len && $data = substr($exp, $start, $len)) {
                    $dataStack->push($data);
                }

                // 将数字指针移到后面
                $start = $i + 1;
                $len = 0;

                if ($opStack->isEmpty() || $this->priority[$char] > $this->priority[$opStack->top()]) {
                    // 当前操作符优先级高于栈顶操作符，则将当前操作符入栈
                    $opStack->push($char);
                } else {
                    // 需要先算出左操作数
                    $this->popAndCalc($dataStack, $opStack, $char);

                    // 操作符入列
                    $opStack->push($char);
                }
            } elseif ($char === '(') {
                // 遇到左括号，取出该子表达式计算并将计算结果压入数据栈，然后跳过该组表达式继续
                $subExp = $this->extractSubGroup($exp, $i);
                // 计算子表达式的值并压入数据栈
                $subRes = $this->operate($subExp);
                $dataStack->push($subRes);

                // 将下标偏移到该子表达式后面
                $i += strlen($subExp) + 1;

                $start = $i + 1;
                $len = 0;
            } else {
                // 数字，将 end 指针后移
                $len++;
            }
        }

        // 遍历完成，将最后一部分的数字压栈
        if ($len > 0) {
            $dataStack->push(substr($exp, $start, $len));
        }

        // 将队列中的剩余操作计算完
        $this->popAndCalc($dataStack, $opStack);

        return $dataStack->pop();
    }

    /**
     * 抽取子组
     * 可以用正则抽取，但此处不用正则
     */
    private function extractSubGroup($str, $start)
    {
        if ($start < 0 || $start >= strlen($str) || $str[$start] !== '(') {
            throw new \Exception("非法操作");
        }

        $tick = 1;
        for ($i = $start + 1; $i < strlen($str); $i++) {
            if ($str[$i] === '(') {
                // 遇到左括号，将计数加 1
                $tick++;
            } elseif ($str[$i] === ')' && --$tick === 0) {
                // 遇到右括号，将计数减 1，当计数为 0，则表示匹配到了正确的右括号
                return substr($str, $start + 1, $i - $start - 1);
            }
        }

        throw new \Exception("非法的表达式");
    }

    /**
     * 循环出栈计算数值，直到栈顶操作符的优先级低于当前优先级，或者操作栈空
     */
    private function popAndCalc(Stack $dataStack, Stack $opStack, string $op = '')
    {
        while (1) {
            if ($opStack->isEmpty() || $op && $this->priority[$op] > $this->priority[$opStack->top()]) {
                break;
            }

            $d1 = $dataStack->pop();
            $d2 = $dataStack->pop();
            $p = $opStack->pop();
            // 计算出的新值压入数据栈
            $dataStack->push($this->calcData($d2, $d1, $p));
        }
    }

    private function calcData($data1, $data2, $op)
    {
        $data1 = intval($data1);
        $data2 = intval($data2);
        switch ($op) {
            case '+':
                return $data1 + $data2;
            case '-':
                return $data1 - $data2;
            case '*':
                return $data1 * $data2;
            case '/':
                return $data1 / $data2;
            case '%':
                return $data1 % $data2;
        }
    }
}

$c = new Calculator();
echo $c->operate("22+3*(((3+5)*5)+4)*41/20");
echo "\n===\n";
echo 22+3*(((3+5)*5)+4)*41/20;