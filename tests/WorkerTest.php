<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 15/8/2017
 * Time: 5:49 PM
 */

namespace lcd344\KirbyQueue\Tests;

use c;
use folder;
use lcd344\KirbyQueue\Job;
use lcd344\KirbyQueue\Queue;
use lcd344\KirbyQueue\Worker;
use PHPUnit\Framework\TestCase;

$kirby = kirby();
$site = site();
$kirby->extensions();
$kirby->models();
$kirby->plugins();

class WorkerTest extends TestCase {

	protected $called = false;

	public function test_can_work_function_job() {

		$filename = $this->make_new_function_job(function () {
			\dir::make(Queue::queuePath() . DS . 'bla.test');
		});
		$file = fopen($filename, 'r+');

		$worker = new Worker(Queue::queuePath(), 1, 1);

		$worker->workOne($file, $filename);

		$this->assertFileExists(Queue::queuePath() . DS . 'bla.test');
		$this->assertFileNotExists($filename);

		\dir::remove(Queue::queuePath() . DS . 'bla.test');
	}

	protected function make_new_function_job($callback) {
		$folder = new folder(Queue::queuePath());
		$files = $folder->files([], true);

		Queue::define('test_job', $callback);

		Queue::dispatch('test_job');

		$folder = new folder(Queue::queuePath());
		foreach ($folder->files([], true) as $file) {
			if (!in_array($file, $files)) {
				$newFile = Queue::queuePath() . DS . $file;
			}
		}

		return $newFile;

	}

	public function test_can_work_class_job() {
		$filename = $this->make_class_job(function () {
			\dir::make(Queue::queuePath() . DS . 'bla.test');
		});
		$file = fopen($filename, 'r+');

		$worker = new Worker(Queue::queuePath(), 1, 1);

		$worker->workOne($file, $filename);

		$this->assertFileExists(Queue::queuePath() . DS . 'bla.test');
		$this->assertFileNotExists($filename);

		\dir::remove(Queue::queuePath() . DS . 'bla.test');

	}

	protected function make_class_job($callback) {
		$folder = new folder(Queue::queuePath());
		$files = $folder->files([], true);

		Queue::define('Test', $callback);
		$job = new Job('Test', []);
		Queue::dispatch($job);

		$folder = new folder(Queue::queuePath());
		foreach ($folder->files([], true) as $file) {
			if (!in_array($file, $files)) {
				$newFile = Queue::queuePath() . DS . $file;
			}
		}

		return $newFile;
	}

	public function test_job_moves_to_fail_on_false() {
		$filename = $this->make_new_function_job(function () {
			return false;
		});
		$file = fopen($filename, 'r+');

		$worker = new Worker(Queue::queuePath(), 1, 1);

		$worker->workOne($file, $filename);

		$this->assertFileExists(Queue::queuePath() . DS . 'failed' . DS . basename($filename));
		$this->assertFileNotExists($filename);

		\f::remove(Queue::queuePath() . DS . 'failed' . DS . basename($filename));

	}

	public function test_job_moves_to_fail_on_thrown_error() {
		$filename = $this->make_new_function_job(function () {
			throw new \Exception('This is an exception');
		});
		$file = fopen($filename, 'r+');

		$worker = new Worker(Queue::queuePath(), 1, 1);

		$worker->workOne($file, $filename);

		$this->assertFileExists(Queue::queuePath() . DS . 'failed' . DS . basename($filename));
		$this->assertFileNotExists($filename);

		\f::remove(Queue::queuePath() . DS . 'failed' . DS . basename($filename));
	}

	public function test_retries_failed_jobs_multiple_times() {
		$filename = $this->make_new_function_job(function () {
			for ($i = 0; $i < 10; $i++) {
				if (!file_exists(Queue::queuePath() . DS . 'test_dir_' . $i)) {
					\dir::make(Queue::queuePath() . DS . 'test_dir_' . $i);

					return false;
				}
			}

			return true;
		});
		$file = fopen($filename, 'r+');

		$worker = new Worker(Queue::queuePath(), 1, 3);

		$worker->workOne($file, $filename);


		$this->assertFileExists(Queue::queuePath() . DS . 'test_dir_0');
		$this->assertFileExists(Queue::queuePath() . DS . 'test_dir_1');
		$this->assertFileExists(Queue::queuePath() . DS . 'test_dir_2');

		for ($i = 0; $i < 10; $i++) {
			if (!file_exists('test_dir_' . $i)) {
				\dir::remove(Queue::queuePath() . DS . 'test_dir_' . $i);
			}
		}
		\f::remove(Queue::queuePath() . DS . 'failed' . DS . basename($filename));
	}

	public function test_calls_global_fail_when_fails() {
		$this->called = false;

		c::set('kirbyQueue.worker.onFail', function ($task,$result) {
			$this->called = true;
		});

		$filename = $this->make_new_function_job(function () {
			return false;
		});
		$file = fopen($filename, 'r+');
		$worker = new Worker(Queue::queuePath(), 1, 1);
		$worker->workOne($file, $filename);

		$this->assertTrue($this->called);
		\f::remove(Queue::queuePath() . DS . 'failed' . DS . basename($filename));
	}


	public function test_calls_onFail_method_on_class_when_fails() {

		$this->expectOutputString('Job Returned False');
		$job = new MockFailClass();

		$folder = new folder(Queue::queuePath());
		$files = $folder->files([], true);

		Queue::dispatch($job);

		$folder = new folder(Queue::queuePath());
		foreach ($folder->files([], true) as $file) {
			if (!in_array($file, $files)) {
				$filename = Queue::queuePath() . DS . $file;
			}
		}

		$file = fopen($filename, 'r+');
		$worker = new Worker(Queue::queuePath(), 1, 1);
		$worker->workOne($file, $filename);
		\f::remove(Queue::queuePath() . DS . 'failed' . DS . basename($filename));
	}

	public function calledOnFail(){
		$this->called = true;
	}
}

class MockFailClass {

	public function handle() {
		return false;
	}

	public function onFail($result){
		echo $result;
	}
}

