<?php
/**
 * Created by PhpStorm.
 * User: lcd34
 * Date: 2/8/2017
 * Time: 11:27 AM
 */

namespace lcd344\KirbyQueue;

use Error;
use Exception;
use Obj;

class Job extends Obj {

	protected $variables;
	protected $function;

	public function __construct($function,$variables) {
		$this->function = $function;
		$this->variables = $variables;
	}

	public function handle(){
		if(! Queue::issetFunction($this->function) || ! is_callable(Queue::get($this->function))){
			throw new Error("Action '{$this->function}' not defined");
		}

		return call_user_func_array(Queue::get($this->function),$this->variables);
	}
}