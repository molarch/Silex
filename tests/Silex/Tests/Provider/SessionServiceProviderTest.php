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

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

/**
 * SessionProvider test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SessionServiceProviderTest extends WebTestCase
{
    public function testRegister()
    {
        $client = $this->createClient();

        $client->request('get', '/login');
        $this->assertEquals('Logged in successfully.', $client->getResponse()->getContent());

        $client->request('get', '/account');
        $this->assertEquals('This is your account.', $client->getResponse()->getContent());

        $client->request('get', '/logout');
        $this->assertEquals('Logged out successfully.', $client->getResponse()->getContent());

        $client->request('get', '/account');
        $this->assertEquals('You are not logged in.', $client->getResponse()->getContent());
    }

    public function createApplication()
    {
        $app = new Application();

        $app->register(new SessionServiceProvider(), [
            'session.test' => true,
        ]);

        $app->get('/login', function () use ($app) {
            $app['session']->set('logged_in', true);

            return 'Logged in successfully.';
        });

        $app->get('/account', function () use ($app) {
            if (!$app['session']->get('logged_in')) {
                return 'You are not logged in.';
            }

            return 'This is your account.';
        });

        $app->get('/logout', function () use ($app) {
            $app['session']->invalidate();

            return 'Logged out successfully.';
        });

        return $app;
    }

    public function testWithRoutesThatDoesNotUseSession()
    {
        $app = new Application();

        $app->register(new SessionServiceProvider(), [
            'session.test' => true,
        ]);

        $app->get('/', function () {
            return 'A welcome page.';
        });

        $app->get('/robots.txt', function () {
            return 'Informations for robots.';
        });

        $app['debug'] = true;
        unset($app['exception_handler']);

        $client = new HttpKernelBrowser($app);

        $client->request('get', '/');
        $this->assertEquals('A welcome page.', $client->getResponse()->getContent());

        $client->request('get', '/robots.txt');
        $this->assertEquals('Informations for robots.', $client->getResponse()->getContent());
    }

    public function testSessionRegister()
    {
        $app = new Application();

        $attrs = new AttributeBag();
        $flash = new FlashBag();
        $app->register(new SessionServiceProvider(), [
            'session.attribute_bag' => $attrs,
            'session.flash_bag' => $flash,
            'session.test' => true,
        ]);

        $session = $app['session'];

        $this->assertSame($flash, $session->getBag('flashes'));
        $this->assertSame($attrs, $session->getBag('attributes'));
    }
}
