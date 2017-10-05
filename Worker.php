<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 6/7/2017
 * Time: 2:46 PM
 */

namespace lcd344\KirbyQueue;


use c;
use Error;
use Exception;
use folder;
use yaml;
use f;

class Worker {

	protected $folder;
	protected $waitTime;
	protected $retries;

	public function __construct($folder, $waitTime,$retries) {
		$this->folder = $folder;
		$this->waitTime = $waitTime;
		$this->retries = $retries;
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
					$this->workOne($file, $filename);
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
		$job = $this->parseFile($file, $filename);
		$task = unserialize($job['job']['class']);
		try {
			if ($task->handle() === false) {
				return [
					'result' => 'Job Returned False',
					'task' => $task
				];
			} else {
				return [
					'result' => true,
					'task' => $task
				];
			}
		} catch(Error $exception){
			return [
				'result' =>  $exception->getMessage(),
				'task' => $task
			];
		} catch(Exception $exception) {
			return [
				'result' =>  $exception->getMessage(),
				'task' => $task
			];
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

	/**
	 * @param $file
	 * @param $filename
	 *
	 * @return array
	 */
	protected function parseFile($file, $filename) {
		rewind($file);
		$content = fread($file, filesize($filename));
		$job = yaml::decode($content);

		return $job;
	}

	/**
	 * @param $file
	 * @param $filename
	 */
	public function workOne($file, $filename) {
		$tries = 0;

		do {
			$result = $this->handleFile($file, $filename);
		} while ($result['result'] !== true && ++$tries < $this->retries);

		if ($result['result'] === true) {
			$this->jobCompleted($file, $filename);
		} else {
			$job = $this->parseFile($file, $filename);
			$this->failedJob($file, $filename, $job, $result['result']);
			$this->onFail($result['task'],$result['result']);
		}
	}

	protected function onFail($task, $result){
		if(method_exists($task,'onFail')){
			$task->onFail($result);
		}

		$failFunction = c::get('kirbyQueue.worker.onFail', false);

		if(is_callable($failFunction)){
			return call_user_func($failFunction,$task,$result);
		}
	}
}