<?php

include './ring_queue.php';

$queue = new RingQueue(10);

for ($i = 0; $i < 22; $i++) {
    $queue->enqueue($i * $i);
    $r = $queue->dequeue();
}
