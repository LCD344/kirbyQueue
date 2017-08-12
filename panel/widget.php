<?php

use lcd344\KirbyQueue\Panel\Controller;

require_once __DIR__ . DS . 'Controller.php';
$kirby->set('widget', 'kirbyQueue', __DIR__ . DS . 'widget');


$router = new \Router([
	[
		'pattern' => 'queue/retry/(:any)',
		'filter' => 'auth',
		'controller' => Controller::class,
		'action' => 'retry'
	],
	[
		'pattern' => 'queue/remove/(:any)',
		'filter' => 'auth',
		'controller' => Controller::class,
		'action' => 'remove'
	]
]);

$route = $router->run(kirby()->path());
// Return if we didn't define a matching route to allow Kirby's router to process the request
if (is_null($route)) return;

require_once __DIR__ . DS . 'Helpers' . DS . 'helpers.php';

\lcd344\KirbyQueue\Panel\Helpers\loadPlugins(['kirbyQueue']);

date_default_timezone_set(kirby()->options['timezone']);
$controller = new $route->controller();

$response = call([$controller,$route->action()], $route->arguments());
// $response is the return value of the route's action, but we won't need that
// Exit execution to stop Kirby from displaying the error page
exit;
