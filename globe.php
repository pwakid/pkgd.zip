<?php

function timeAgo($timestamp) {
    $time = time() - strtotime($timestamp);

    $units = [
        "year"   => 365 * 24 * 60 * 60,
        "month"  => 30 * 24 * 60 * 60,
        "week"   => 7 * 24 * 60 * 60,
        "day"    => 24 * 60 * 60,
        "hour"   => 60 * 60,
        "minute" => 60,
        "second" => 1
    ];

    foreach ($units as $name => $duration) {
        if ($time >= $duration) {
            $count = floor($time / $duration);
            return "$count $name" . ($count > 1 ? "s" : "") . " ago";
        }
    }

    return "Just now";
}


$basedir = __DIR__;
$dir = $_GET['dir'];
$file = $_GET['file'];
$globe = isset($_GET['dir']) ? __DIR__.'/'.$_GET['dir'] : __DIR__;
$lastdir = substr($dir, 0, strrpos($dir, '/'));

if(is_dir($globe))
{
  $files = glob($globe.'/*'); // List all files and directories in the specified path
}
?>
