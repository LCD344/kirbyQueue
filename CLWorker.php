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

$folder = c::get('kirbyQueue.queue.folder', $kirby->roots()->site() . DS . 'queue');
$waitTime = 1;
if(isset(getopt('w::')['w'])){
	$waitTime = getopt('w::')['w'];
}

$worker = new lcd344\KirbyQueue\Worker($folder,$waitTime);
$worker->work();