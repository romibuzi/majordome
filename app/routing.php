<?php

/**
 * All routes are defined here
 *
 * @link http://silex.sensiolabs.org/doc/usage.html#routing
 * @link http://silex.sensiolabs.org/doc/providers/service_controller.html
 */

$app->get('/', 'default_controller:indexAction')->bind('index');
$app->get('/run/{id}', 'default_controller:runDetailsAction')->bind('run_details');
