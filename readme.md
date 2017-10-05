# Kirby Queue

![Version](https://img.shields.io/badge/version-1.1.0-green.svg) ![License](https://img.shields.io/badge/license-MIT-green.svg) ![Kirby Version](https://img.shields.io/badge/Kirby-2.2.4%2B-red.svg)

*Version 1.1.0*

This is a plugin to make a job queue for kirby CMS.

## Installation

### 1. Clone or download

1. [Clone](https://github.com/LCD344/kirbyQueue.git) or [download](https://github.com/LCD344/kirbyQueue/archive/master.zip)  this repository.
2. Unzip the archive if needed and rename the folder to `kirbyQueue`.

**Make sure that the plugin folder structure looks like this:**

```
site/plugins/kirbyQueue/
```

### 2. Git Submodule

If you know your way around Git, you can download this plugin as a submodule:

```
$ cd path/to/kirby
$ git submodule add https://github.com/LCD344/kirbyQueue.git site/plugins/kirbyQueue
```

## Setup

Add a jobs and queue/failed folders to your site directory - it should look like this:


```
site/jobs/
site/queue/failed/
```

In the jobs directory you can create any job you would want as a php class, the important thing would be to have a handle method - as this will be the entrance point for the worker.

if you want to have a custom title appear when the job fails, then define a getTitle function that will return that title.

Additionally you can have a special onFail method that will run for the job once it's moved to the failed jobs directory (after retrying x times). The method will receive a string with the reason for failure

For example

```php
class Job1 {

  protected $id;

  public function __construct($id) {
    $this->id = $id;
  }

  public function handle(){
    sleep(1);
    echo $this->id . PHP_EOL;
  }
  
  public function getTitle(){
    return 'job title';
  }
  
  public function onFail($message){
    echo $message;
  }
}
```

Alternatively you can define a job as a function as in


```php
\lcd344\KirbyQueue\Queue::define('job1', function($sleep,$wait) {
  sleep($sleep);
  echo $text;
});
```

then to run the worker you just need to run `site/plugins/kirbyQueue/CLWorker.php` from commandline.

The commandline will automatically load the config.php file, if you need to load any other config files, then you can pass it the name of the server using the --server flag. For example if you need a config.example.com.php then run the next command

`php site/plugins/kirbyQueue/CLWorker.php --server=example.com`

This will create one worker that would work forever. This queue supports multiple workers working at the same time using the flock command.

it is recommended to run the workers as daemon. Or using a tool similar to [supervisor](http://supervisord.org/).

## Usage

Once you are done setting up the workers, usage would require you to dispatch a new job object.

for example:

```php
\lcd344\KirbyQueue\Queue::dispatch(new Job1(uniqid()));
```

or if the job was defined as a function you can do

```php
\lcd344\KirbyQueue\Queue::dispatch('job1',[
  10,
  'test'
],'Job Title');
```

the order of variables in the array corresponds to the functions variables as the function uses call_user_func_array to run it.

The job title will show in the panel in case the job fails.

## Options

The following options can be set in your `/site/config/config.php` file:

```php
c::set('kirbyQueue.queue.folder', 'path/to/queue/folder'); // This will change the queue folder

c::set('kirbyQueue.jobs.folder', 'path/to/jobs/folder'); // This will change the path to the jobs folder

c::set('kirbyQueue.queue.wait',1); // this will set the amount of time to wait between getting new jobs

c::set('kirbyQueue.queue.retries',3); // this will set the amount of times a job will be retired before it's sent to the failed folder

c::set('kirbyQueue.worker.onFail', function ($task,$message) {
    // notify that tasks are failing
}); // this function will be called after any task has failed, it will recieve the failed task, and the error message. 

```

## Changelog

**1.1.0**
- Added on fail function


**1.0.0**
- Added testing

**0.5.0**
- Added a widget that shows failed jobs and allows to retry or to cancel them
- Added support for job title to show when the job is in the failed list

**0.3.0**
- Added a support to retry failed to jobs before sending to failed folder

**0.2.0**

- Added a way to make jobs as only a function
- refactored code
- added tracking for jobs failure

**0.1.0**
- Initial release

## Requirements

- [**Kirby**](https://getkirby.com/) 2.4+

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/username/plugin-name/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

## Credits

- [Jens Törnell](https://github.com/jenstornell) for the readme boilerplate.
