<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 14/6/2017
 * Time: 4:20 PM
 */

namespace lcd344\KirbyQueue;


use c;
use yaml;
use Exception;
use f;
use Kirby;

class Queue {

	static public function dispatch($job) {

		$folder = c::get('kirbyQueue.queue.folder', kirby::instance()->roots()->site() . DS . 'queue');
		$file = $folder . DS . uniqid('job_') . '.yml';

		$data = ['job' => [
			'status' => 'active',
			'class' => serialize($job)
		]];


		if (!f::write($file, yaml::encode($data))) {
			throw new Exception("Can't write to queue file");
		}

		return true;
	}
}