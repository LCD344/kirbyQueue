<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 6/7/2017
 * Time: 2:46 PM
 */

namespace lcd344\KirbyQueue;


use Error;
use Exception;
use folder;
use yaml;
use f;

class Worker {

	protected $folder;
	protected $waitTime;

	public function __construct($folder, $waitTime) {

		$this->folder = $folder;
		$this->waitTime = $waitTime;
	}

	public function work() {

		while (true) {
			$folder = new folder($this->folder);
			$files = array_values($folder->files(null, true));
			$count = 0;
			$lock = false;
			while (!$lock && $count < count($files)) {
				$filename = $this->folder . DS . $files[$count];
				$file = fopen($filename, 'r+');
				$lock = $this->getLock($file, $filename);

				if ($lock) {
					$this->handleFile($file, $filename);
				}
				$count++;
			}

			sleep($this->waitTime);
		}
	}

	/**
	 * @param $file
	 * @param $filename
	 *
	 * @return bool
	 */
	protected function getLock($file, $filename) {
		if ($file && filesize($filename) != 0) {
			$lock = flock($file, LOCK_EX | LOCK_NB);
		} else {
			$lock = false;
		}

		return $lock;
	}

	/**
	 * @param $file
	 * @param $filename
	 */
	protected function handleFile($file, $filename) {
		$content = fread($file, filesize($filename));
		$job = yaml::decode($content);
		try {
			$task = unserialize($job['job']['class']);
			if ($task->handle() === false) {
				$this->failedJob($file, $filename, $job, 'Job Returned False');
			} else {
				$this->jobCompleted($file, $filename);
			}
		} catch(Error $exception){
			$this->failedJob($file, $filename, $job, $exception->getMessage());
		} catch(Exception $exception) {
			$this->failedJob($file, $filename, $job, $exception->getMessage());
		}
	}

	protected function failedJob($file, $filename, $job, $error) {
		ftruncate($file, 0);
		fclose($file);
		$newName = substr_replace($filename, DS . 'failed', strrpos($filename, DS), 0);
		$job['job']['error'] = $error;
		$job['job']['tried'] = date('c');
		f::move($filename,$newName);
		yaml::write($newName, $job);
	}

	/**
	 * @param $file
	 * @param $filename
	 */
	protected function jobCompleted($file, $filename) {
		ftruncate($file, 0);
		fclose($file);
		f::remove($filename);
	}
}