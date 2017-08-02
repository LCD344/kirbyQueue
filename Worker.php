<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 6/7/2017
 * Time: 2:46 PM
 */

namespace lcd344\KirbyQueue;


use c;
use yaml;
use folder;
use kirby;

class Worker {

	public function work() {

		while (true) {
			$folder = new folder(c::get('kirbyQueue.queue.folder', kirby::instance()->roots()->site() . DS . 'queue'));
			$files = $folder->scan();
			$count = 2;
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
			exit();
		}
	}

	protected function failedJob($file,$filename,$job){
		$job['job']['status'] = 'failed';
		ftruncate($file,0);
		fwrite($file,yaml::encode($job));
		fclose($file);
		$newName = substr_replace($filename,'.failed.yml',strrpos($filename,'.'));
		rename($filename,$newName);
		file_put_contents($newName,yaml::encode($job));
	}
}