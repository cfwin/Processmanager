<?php
$sleep_time=rand(1,10);
sleep($sleep_time);
file_put_contents("temp/".$argv[1].'.txt', time());
