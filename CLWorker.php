<?php

use lcd344\KirbyQueue\Queue;

define('DS', DIRECTORY_SEPARATOR);


if(isset(getopt('',['bootstrap:'])['bootstrap'])){
	require(getopt('',['bootstrap:'])['bootstrap'] . DS . 'bootstrap.php');
} else {
	require('kirby/bootstrap.php');
}

if(isset(getopt('',['server:'])['server'])) {
	$_SERVER['SERVER_NAME'] = getopt('',['server:'])['server'];
}

$kirby = kirby();
$site  = site();

$kirby->configure();
$kirby->extensions();
$kirby->models();
$kirby->plugins();


$folder = Queue::queuePath();
$waitTime = c::get('kirbyQueue.queue.wait',1);
if(isset(getopt('',['wait:'])['wait'])){
	$waitTime = getopt('',['wait:'])['wait'];
}

$retries = c::get('kirbyQueue.queue.retries',3);
if(isset(getopt('',['retries:'])['retries'])){
	$retries = getopt('retries::')['retries'];
}

$worker = new lcd344\KirbyQueue\Worker($folder,$waitTime,$retries);
$worker->work();