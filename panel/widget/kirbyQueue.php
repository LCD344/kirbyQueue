<?php


use lcd344\KirbyQueue\Queue;

if (!panel()->user()->isAdmin()) return false;
$jobsCount = \lcd344\KirbyQueue\Queue::count();

// Don't show the Widget if there are no failed jobs,
// but DO display it if there are more than 5 jobs piled up
// to remind the user there is a queue at all

return [
		'title' => 'Queue',
		'options' => [[
			'text' => "{$jobsCount} jobs in queue",
			'icon' => '',
			'link' => ''
		]],
		'html' => function () {
			$failedJobs = Queue::failedJobs();
			return tpl::load(__DIR__ . DS . 'kirbyQueue.html.php', compact('failedJobs'));
		}
	];