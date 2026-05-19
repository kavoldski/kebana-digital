<?php
$file = 'C:/XAMPP/apache/logs/error.log';
if (file_exists($file)) {
    $lines = file($file);
    $last_lines = array_slice($lines, -100);
    echo implode("", $last_lines);
} else {
    echo "Log file not found.";
}
