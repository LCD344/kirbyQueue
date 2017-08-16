<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 15/8/2017
 * Time: 3:40 PM
 */

namespace lcd344\KirbyQueue\Tests;

use f;
use folder;
use lcd344\KirbyQueue\Job;
use lcd344\KirbyQueue\Queue;
use PHPUnit\Framework\TestCase;
use yaml;

$kirby = kirby();
$site = site();
$kirby->extensions();
$kirby->models();
$kirby->plugins();

class QueueTest extends TestCase {

	public function test_can_add_function_jobs() {
		$folder = new folder(Queue::queuePath());
		$files = $folder->files([], true);
		Queue::dispatch('job', ['info'], 'Test');

		$folder = new folder(Queue::queuePath());
		$this->assertEquals(
			count($folder->files()),
			count($files) + 1
		);

		foreach ($folder->files([], true) as $file) {
			if (!in_array($file, $files)) {
				$file = Queue::queuePath() . DS . $file;
				$job = yaml::read($file)['job'];
				f::remove($file);
			}
		}

		$this->assertEquals('Test', $job['title']);
		$this->assertEquals(serialize(new Job('job', ['info'])), $job['class']);
	}

	public function test_can_add_class_job(){
		$folder = new folder(Queue::queuePath());
		$files = $folder->files([], true);
		$job = new Job('Test',[]);


		Queue::dispatch('job');

		$folder = new folder(Queue::queuePath());
		$this->assertEquals(
			count($folder->files()),
			count($files) + 1
		);

		foreach ($folder->files([], true) as $file) {
			if (!in_array($file, $files)) {
				$file = Queue::queuePath() . DS . $file;
				$job = yaml::read($file)['job'];
				f::remove($file);
			}
		}

		$this->assertEquals('job', $job['title']);

	}

	public function test_can_count_jobs(){
		$folder = new folder(Queue::queuePath());

		$this->assertEquals(
			count($folder->files()),
			Queue::count()
		);
	}

	public function test_returns_failed_jobs(){
		$count = count($failedJobs = Queue::failedJobs());
		$failedJob = $this->makeFailedJob();
		$failedJobs = Queue::failedJobs();

		$content = file_get_contents($failedJob);
		$this->assertArraySubset([$count => [
			'job' => yaml::decode($content),
			'file' =>basename($failedJob)
		]],$failedJobs);

		f::remove($failedJob);
	}

	public function test_retries_failed_job(){
		$failedJob = basename($this->makeFailedJob());
		Queue::retry($failedJob);

		$this->assertFileExists(Queue::queuePath() . DS . $failedJob);

		f::remove(Queue::queuePath() . DS . $failedJob);
	}


	public function test_deletes_failed_job(){
		$failedJob = $this->makeFailedJob();
		Queue::remove(basename($failedJob));

		$this->assertFileNotExists($failedJob);

	}

	protected function makeFailedJob(){
		$folder = new folder(Queue::queuePath());
		$files = $folder->files([], true);
		Queue::dispatch('job', ['info'], 'Test');
		$folder = new folder(Queue::queuePath());
		foreach ($folder->files([], true) as $file) {
			if (!in_array($file, $files)) {
				$oldFile = Queue::queuePath() . DS . $file;
				$newFile = Queue::queuePath() . DS . 'failed' . DS . $file;
				f::move($oldFile,$newFile);
			}
		}

		return $newFile;
	}

}
