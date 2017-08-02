<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 6/7/2017
 * Time: 2:46 PM
 */

namespace lcd344\KirbyQueue;


use c;
use dir;
use yaml;
use f;
use kirby;

class Worker {

	public function work() {

		$folder = c::get('kirbyQueue.queue.folder', kirby::instance()->roots()->site() . DS . 'queue');

		while (true) {
			$files = dir::read($folder);
			$count = 3;
			$lock = false;
			while(!$lock && $count < count($files)+2) {
				$filename = $folder . DS . $files[$count];
				$file = fopen($filename,'r+');
				if($file && filesize($filename) && strpos($files[$count],'.failed.yml') == false){
					$lock = flock($file, LOCK_EX  | LOCK_NB);
				} else {
					$lock = false;
				}

				if ($lock) {
					$content = fread($file, filesize($filename));
					$job = yaml::decode($content);
					try {
						if($job['job']['status'] == 'active'){
							$task = unserialize($job['job']['class']);
							if($task->handle() === false){
								$this->failedJob($file,$filename,$job);
							} else {
								ftruncate($file,0);
								fclose($file);
								unlink($filename);
							}
						}
					} catch (\Exception $exception){
						$this->failedJob($file,$filename,$job);
					}
				}
				$count++;
			}

			if(isset(getopt('w::')['w'])){
				sleep(getopt('w::')['w']);
			} else {
				sleep(1);
			}
		}
	}

	protected function failedJob($file,$filename,$job){
		ftruncate($file,0);
		fclose($file);
		$newName = substr_replace($filename,DS . 'failed',strrpos($filename,DS),0);
		rename($filename,$newName);
		f::write($newName, yaml::encode($job));
	}
}