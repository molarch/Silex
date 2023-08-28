<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Provider;

use Symfony\Component\Mime\Email;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Silex\Provider\MailerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;

class MailerServiceProviderTest extends TestCase
{
    public function testMailerServiceIsMailer()
    {
        $app = new Application();

        $app->register(new MailerServiceProvider());
        $app->boot();

        $this->assertInstanceOf(MailerInterface::class, $app['mailer']);
    }

    public function testMailerSendsMailsOnFinish()
    {
        $app = new Application();

        $app->register(new MailerServiceProvider());
        $app->boot();

        $app->get('/', function () use ($app) {
            $app['mailer']->send(new Email());
        });
    }
}
