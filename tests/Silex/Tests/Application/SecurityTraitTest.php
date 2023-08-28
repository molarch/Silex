<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Application;

use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use PHPUnit\Framework\TestCase;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecurityTraitTest extends TestCase
{
    public function testHashPassword()
    {
        $app = $this->createApplication([
            'fabien' => ['ROLE_ADMIN', '$2y$15$lzUNsTegNXvZW3qtfucV0erYBcEqWVeyOmjolB7R1uodsAVJ95vvu'],
        ]);

        $password = 'bar';
        $hashed = $app['security.user_password_hasher']->hashPassword($user, $password);
        $user = new InMemoryUser('foo', $hashed);

        $this->assertTrue(
            $app['security.user_password_hasher']->isPasswordValid($user, $password)
        );
    }

    public function testIsGrantedWithoutTokenThrowsException()
    {
        $this->expectException(AuthenticationCredentialsNotFoundException::class);
        $app = $this->createApplication();
        $app->get('/', function () { return 'foo'; });
        $app->handle(Request::create('/'));
        $app->isGranted('ROLE_ADMIN');
    }

    public function testIsGranted()
    {
        $request = Request::create('/');

        $app = $this->createApplication([
            'fabien' => ['ROLE_ADMIN', '$2y$15$lzUNsTegNXvZW3qtfucV0erYBcEqWVeyOmjolB7R1uodsAVJ95vvu'],
            'monique' => ['ROLE_USER',  '$2y$15$lzUNsTegNXvZW3qtfucV0erYBcEqWVeyOmjolB7R1uodsAVJ95vvu'],
        ]);
        $app->get('/', function () { return 'foo'; });

        // User is Monique (ROLE_USER)
        $request->headers->set('PHP_AUTH_USER', 'monique');
        $request->headers->set('PHP_AUTH_PW', 'foo');
        $app->handle($request);
        $this->assertTrue($app->isGranted('ROLE_USER'));
        $this->assertFalse($app->isGranted('ROLE_ADMIN'));

        // User is Fabien (ROLE_ADMIN)
        $request->headers->set('PHP_AUTH_USER', 'fabien');
        $request->headers->set('PHP_AUTH_PW', 'foo');
        $app->handle($request);
        $this->assertFalse($app->isGranted('ROLE_USER'));
        $this->assertTrue($app->isGranted('ROLE_ADMIN'));
    }

    public function createApplication($users = [])
    {
        $app = new SecurityApplication();
        $app->register(new SecurityServiceProvider(), [
            'security.firewalls' => [
                'default' => [
                    'http' => true,
                    'users' => $users,
                ],
            ],
        ]);

        return $app;
    }
}
