<?php

require_once 'Queue.php';
require_once 'Worker.php';

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