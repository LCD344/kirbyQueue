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
use f;
use kirby;
use yaml;
use folder;

class Queue {

	private static $actions = [];

	/**
	 * Defines an action to perform when job is worked on
	 *
	 * @param  string    Name of the action
	 * @param  Callable  Closure with the action
	 */

	public static function jobsPath(){
		return c::get('kirbyQueue.jobs.folder', kirby::instance()->roots()->site() . DS . 'jobs');
	}


	public static function queuePath(){
		return c::get('kirbyQueue.queue.folder', kirby::instance()->roots()->site() . DS . 'queue');
	}

	public static function define($name, $action) {
		static::$actions[$name] = $action;
	}

	public static function issetFunction($name) {
		return isset(static::$actions[$name]);
	}


	public static function get($name) {
		return static::$actions[$name];
	}

	public static function count() {
		$folder = new folder(static::queuePath());
		$files = $folder->files();

		return count($files);
	}

	public static function failedJobs() {
		$failedJobs = [];
		$folder = new folder(static::queuePath() . DS . "failed");

		foreach ($folder->files() as $file) {
			$content = file_get_contents($file);
			$failedJobs[] = [
				'job' => yaml::decode($content),
				'file' => substr($file,strrpos($file,DS) + 1)
			];
		}

		return $failedJobs;
	}

	static public function dispatch($job, $data = [],$title = false) {

		$jobData = ['job' => [
			'added' => date('c'),
		]];

		if (is_object($job)) {
			$jobData['job']['class'] = serialize($job);
			if(method_exists($job,'getTitle')){
				$jobData['job']['title'] = $job->getTitle();
			} else {
				$jobData['job']['title'] = get_class($job);
			}
		} else {
			$class = new Job($job, $data);
			$jobData['job']['class'] = serialize($class);
			if($title){
				$jobData['job']['title'] = $title;
			} else {
				$jobData['job']['title'] = $job;
			}
		}

		$folder = static::queuePath();
		$file = $folder . DS . uniqid('job_') . '.yml';


		if (!yaml::write($file, $jobData)) {
			throw new Error("Can't write to queue file");
		}

		return true;
	}

	static public function remove($file){
		f::remove(static::queuePath() . DS . "failed" . DS . $file);
	}

	static public function retry($file){
		$old = static::queuePath() . DS . "failed" . DS . $file;
		$new = static::queuePath() . DS  . $file;
		f::move($old,$new);
	}
}