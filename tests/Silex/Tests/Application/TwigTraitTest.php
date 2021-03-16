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

use PHPUnit\Framework\TestCase;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TwigTraitTest extends TestCase
{
    public function testRender(): void
    {
        $app = $this->createApplication();

        $app['twig'] = $mailer = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())->method('render')->willReturn('foo');

        $response = $app->render('view');
        $this->assertEquals(Response::class, get_class($response));
        $this->assertEquals('foo', $response->getContent());
    }

    public function testRenderKeepResponse(): void
    {
        $app = $this->createApplication();

        $app['twig'] = $mailer = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())->method('render')->willReturn('foo');

        $response = $app->render('view', [], new Response('', 404));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRenderForStream()
    {
        $app = $this->createApplication();

        $app['twig'] = $mailer = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())->method('display')->willReturnCallback(function () {
            echo 'foo';
        });

        $response = $app->render('view', [], new StreamedResponse());
        $this->assertEquals(StreamedResponse::class, get_class($response));

        ob_start();
        $response->send();
        $this->assertEquals('foo', ob_get_clean());
    }

    public function testRenderView(): void
    {
        $app = $this->createApplication();

        $app['twig'] = $mailer = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())->method('render');

        $app->renderView('view');
    }

    public function createApplication()
    {
        $app = new TwigApplication();
        $app->register(new TwigServiceProvider());

        return $app;
    }
}
