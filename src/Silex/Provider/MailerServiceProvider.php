<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

/**
 * Mailer Provider.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MailerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['mailer.dsn'] = 'smtp://localhost';

        $app['mailer.initialized'] = false;

        $app['mailer'] = function ($app) {
            $app['mailer.initialized'] = true;
            return new Mailer($app['mailer.transport']);
        };

        $app['mailer.transport'] = function ($app) {
            return Transport::fromDsn($app['mailer.dsn']);
        };
    }
}
