<?php

include "./stack.php";

$stack = new Stack();
$stack->push("a");
$stack->push("b");
$stack->push("c");
echo $stack->pop() . "\n";
echo $stack->pop() . "\n";
echo $stack->pop() . "\n";