<?php
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
