<?php


if(class_exists('Panel')) require(__DIR__ . DS . 'panel' . DS . 'init.php');

require_once 'Queue.php';
require_once 'Worker.php';
require_once 'Job.php';

$folder = new folder(c::get('kirbyQueue.jobs.folder', kirby::instance()->roots()->site() . DS . 'jobs'));
$files = $folder->files();
foreach ($files as $file){
	require_once $file;
}


/*
use lcd344\KirbyQueue\Queue;

Queue::dispatch(new Job1(uniqid()));
Queue::dispatch(new Job1(uniqid()));

exit();*/