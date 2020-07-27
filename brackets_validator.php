<?php

include './stack.php';

/**
 * 通过栈实现字符串括号匹对校验
 * 支持的括号：[]、{}、()
 * 简单起见，这里不考虑中文
 */
class BracketsValidator
{
    private $map = ['[' => ']', '{' => '}', '(' => ')'];
    public function validate($str)
    {
        $stack = new Stack();
        for ($i = 0; $i < strlen($str); $i++) {
            if (array_key_exists($str[$i], $this->map)) {
                // 左括号，入栈
                $stack->push($str[$i]);
            } elseif (in_array($str[$i], $this->map)) {
                // 右括号，出栈并比较
                if ($stack->isEmpty() || $str[$i] != $this->map[$stack->pop()]) {
                    throw new \Exception("invalid str");
                }
            }
        }

        // 如果栈中还有元素，则不符合
        if (!$stack->isEmpty()) {
            throw new \Exception("invalid str 2");
        }
    }
}

$validator = new BracketsValidator();
$validator->validate("this {is [{a duck on (the floor)}]}");