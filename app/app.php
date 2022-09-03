<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

function is_cli()
{
    if (empty($_SERVER['REMOTE_ADDR']) && count($_SERVER['argv']) > 0) {
        return true;
    }
    return false;
}

$env = getenv('ENV') ?: 'prod';

if (!file_exists(__DIR__ . '/config.php')) {
    throw new \LogicException('app/config.php.dist must be copied inside app/config.php and edited');
}
$config = require_once __DIR__ . '/config.php';

if (empty($config['aws.region']) || empty($config['aws.accountId'])) {
    throw new \LogicException('aws.region and aws.accountId must be set inside app/config.php');
}

$app = new Silex\Application($config);

if ($env === 'test') {
    $app['debug'] = true;
}

$dbPath = 'test' === $env ? $config['db_test.path'] : $config['db.path'];

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver'   => 'pdo_sqlite',
        'path'     => $dbPath,
    ],
]);

if (!file_exists($dbPath)) {
    $app['logger']->info("'$dbPath' doesnt exist, creating it with schema." . PHP_EOL);

    $schema = file_get_contents(__DIR__ . '/schema.sql');
    // split each CREATE TABLE queries and run them inside the database
    $queries = explode(';', $schema);
    foreach ($queries as $query) {
        $app['db']->exec($query);
    }
}

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => $config['views.path'],
    'twig.options' => [
        'debug' => $config['debug']
    ]
]);

$app->register(new Silex\Provider\MonologServiceProvider(), [
    'monolog.level'   => $config['monolog.level'],
    'monolog.logfile' => $config['monolog.logfile'],
    'monolog.name'    => $config['monolog.name'],
]);

if ($app['debug'] && is_cli()) {
    // also push logs to stdout
    $app->extend('logger', function (Monolog\Logger $logger) {
        $logger->pushHandler(new Monolog\Handler\StreamHandler(fopen('php://stdout', 'w')));
        return $logger;
    });
}

// AWS Configuration
$app['aws.sdk'] = new Aws\Sdk([
    'version' => 'latest',
    'region'  => $config['aws.region']
]);

$app['run.manager'] = new Majordome\Manager\RunManager(
    $app['db'],
    new Majordome\Resource\AWSResourceUrlGenerator($config['aws.region'])
);

// Register controllers as services
// @link http://silex.sensiolabs.org/doc/providers/service_controller.html
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app['default_controller'] = function () use ($app, $config) {
    return new Majordome\Controller\DefaultController($app['run.manager'], $app['twig'], $app['logger']);
};

$app['report.sender_adress'] = $config['report.sender_adress'];
$app->register(new Silex\Provider\SwiftmailerServiceProvider(), [
    'swiftmailer.use_spool' => false,
    'swiftmailer.options'   => $config['report.email_configuration']
]);

// Include routing
include __DIR__ . '/routing.php';

return $app;
