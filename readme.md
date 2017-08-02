# Kirby Boiler Readme

![Version](https://img.shields.io/badge/version-0.1.0-green.svg) ![License](https://img.shields.io/badge/license-MIT-green.svg) ![Kirby Version](https://img.shields.io/badge/Kirby-2.2.4%2B-red.svg)

*Version 0.1.0*

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
}
```

then to run the worker you just need to run `site/plugins/kirbyQueue/CLWorker.php` from commandline.

This will create one worker that would work forever. This queue supports multiple workers working at the same time using the flock command.

it is recommended to run the workers as daemon. Or using a tool similar to [supervisor](http://supervisord.org/).

## Usage

Once you are done setting up the workers, usage would require you to dispatch a new job object.

for example:

```php
Queue::dispatch(new Job1(uniqid()));
```

## Options

The following options can be set in your `/site/config/config.php` file:

```php
c::set('kirbyQueue.queue.folder', 'path/to/queue/folder'); // This will change the queue folder
c::set('kirbyQueue.jobs.folder', 'path/to/jobs/folder'); // This will change the path to the jobs folder
```

## Changelog

**0.1.0**
- Initial release

## Todo

- [ ] Improve worker code structure to different functions
- [ ] Support job definition as functions
- [ ] Add unit tests
- [ ] Add jobs retry option
- [ ] Add jobs widget

## Requirements

- [**Kirby**](https://getkirby.com/) 2.4+

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/username/plugin-name/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

## Credits

- [Jens Törnell](https://github.com/jenstornell) for the readme boilerplate.
