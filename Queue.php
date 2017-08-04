<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 14/6/2017
 * Time: 4:20 PM
 */

namespace lcd344\KirbyQueue;


use c;
use Error;
use yaml;

class Queue {

	private static $actions = [];
	/**
	 * Defines an action to perform when job is worked on
	 * @param  string    Name of the action
	 * @param  Callable  Closure with the action
	 */

	public static function define($name, $action) {
		static::$actions[$name] = $action;
	}

	public static function issetFunction($name) {
		return isset(static::$actions[$name]);
	}


	public static function get($name) {
		return static::$actions[$name];
	}


	static public function dispatch($job,$data = null) {

		$jobData = ['job' => [
			'added' => date('c'),
			'type' => 'object',
			'class' => serialize($job)
		]];

		if(is_object($job)){
			$jobData['job']['class'] = serialize($job);
		} else {
			$class = new Job($job,$data);
			$jobData['job']['class'] = serialize($class);
		}

		$folder = c::get('kirbyQueue.queue.folder', kirby()->roots()->site() . DS . 'queue');
		$file = $folder . DS . uniqid('job_') . '.yml';


		if (! yaml::write($file, $jobData)) {
			throw new Error("Can't write to queue file");
		}

		return true;
	}

}