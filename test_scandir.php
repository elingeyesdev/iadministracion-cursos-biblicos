<?php

$dir = 'resources/views';
if (is_dir($dir)) {
    $files = scandir($dir);
    print_r($files);
} else {
    echo 'not a dir';
}