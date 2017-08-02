<?php

require_once 'Queue.php';
require_once 'Worker.php';
require_once 'Job.php';

$folder = new folder(c::get('kirbyQueue.jobs.folder', kirby::instance()->roots()->site() . DS . 'jobs'));
$files = $folder->files();
foreach ($files as $file){
	require_once $file;
}