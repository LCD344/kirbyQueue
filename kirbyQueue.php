<?php

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

Queue::define('func1',function($sleep,$text){
	sleep($sleep);
	echo $text;
});
Queue::define('failed Job',function($sleep,$text){
	throw new Exception('nur sends an error');
});

Queue::dispatch('func1',[
	10,'nur'
]);

Queue::dispatch('failed Job',[
	10,'nur'
]);

Queue::dispatch('dla',[
	10,'nur'
]);

Queue::dispatch(new Job1(uniqid()));
Queue::dispatch(new Job1(uniqid()));

exit();*/