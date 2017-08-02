<?php

define('DS', DIRECTORY_SEPARATOR);


if(isset(getopt('r::')['r'])){
	require(getopt('r::')['r'] . DS . 'bootstrap.php');
} else {
	require('kirby/bootstrap.php');
}
$kirby = kirby();
$site  = site();

$kirby->extensions();
$kirby->models();
$kirby->plugins();


$worker = new lcd344\KirbyQueue\Worker();
$worker->work();