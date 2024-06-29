<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

require dirname(__DIR__).'/vendor/autoload.php';

// https://github.com/symfony/symfony/issues/53812
set_exception_handler([new ErrorHandler(), 'handleException']);

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

echo "Resetting test database..." . PHP_EOL;
passthru(sprintf(
    'php "%s/../bin/console" doctrine:database:drop --env=test --force --no-interaction',
    __DIR__
));
passthru(sprintf(
    'php "%s/../bin/console" doctrine:database:create --env=test --no-interaction',
    __DIR__
));
passthru(sprintf(
    'php "%s/../bin/console" doctrine:migrations:migrate --env=test --no-interaction',
    __DIR__
));
passthru(sprintf(
    'php "%s/../bin/console" doctrine:fixtures:load --env=test --no-interaction',
    __DIR__
));
echo " Done" . PHP_EOL;
