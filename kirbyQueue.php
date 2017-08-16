<?php


require_once 'Queue.php';
require_once 'Worker.php';
require_once 'Job.php';

if(class_exists('Panel')) {
	require(__DIR__ . DS . 'panel' . DS . 'widget.php');
}

$folder = new folder(lcd344\KirbyQueue\Queue::jobsPath());
$files = $folder->files();
foreach ($files as $file){
	require_once $file;
}


/*
use lcd344\KirbyQueue\Queue;

Queue::dispatch(new Job1(uniqid()));
Queue::dispatch(new Job1(uniqid()));

exit();*/