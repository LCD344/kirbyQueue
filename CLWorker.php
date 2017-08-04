<?php

define('DS', DIRECTORY_SEPARATOR);


if(isset(getopt('b::')['b'])){
	require(getopt('b::')['b'] . DS . 'bootstrap.php');
} else {
	require('kirby/bootstrap.php');
}

$kirby = kirby();
$site  = site();

$kirby->extensions();
$kirby->models();
$kirby->plugins();

$folder = c::get('kirbyQueue.queue.folder', $kirby->roots()->site() . DS . 'queue');
$waitTime = c::get('kirbyQueue.queue.wait',1);
if(isset(getopt('w::')['w'])){
	$waitTime = getopt('w::')['w'];
}

$retries = c::get('kirbyQueue.queue.retries',3);
if(isset(getopt('r::')['r'])){
	$retries = getopt('r::')['r'];
}

$worker = new lcd344\KirbyQueue\Worker($folder,$waitTime,$retries);
$worker->work();