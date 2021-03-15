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

use Fig\Link\Link;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\AssetServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Extension\CoreExtension;
use Symfony\Component\Form\FormRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Twig\Loader\LoaderInterface;

/**
 * TwigProvider test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class TwigServiceProviderTest extends TestCase
{
    public function testRegisterAndRender(): void
    {
        $app = new Application();

        $app->register(new TwigServiceProvider(), [
            'twig.templates' => ['hello' => 'Hello {{ name }}!'],
        ]);

        $app->get('/hello/{name}', function ($name) use ($app) {
            return $app['twig']->render('hello', ['name' => $name]);
        });

        $request = Request::create('/hello/john');
        $response = $app->handle($request);
        self::assertEquals('Hello john!', $response->getContent());
    }

    public function testLoaderPriority(): void
    {
        $app = new Application();
        $app->register(new TwigServiceProvider(), [
            'twig.templates' => ['foo' => 'foo'],
        ]);
        $loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        if (method_exists(LoaderInterface::class, 'getSourceContext')) {
            $loader->expects(self::never())->method('getSourceContext');
        }
        $app['twig.loader.filesystem'] = function ($app) use ($loader) {
            return $loader;
        };
        self::assertEquals('foo', $app['twig.loader']->getSourceContext('foo')->getCode());
    }

    public function testHttpFoundationIntegration(): void
    {
        $app = new Application();
        $app['request_stack']->push(Request::create('/dir1/dir2/file'));
        $app->register(new TwigServiceProvider(), [
            'twig.templates' => [
                'absolute' => '{{ absolute_url("foo.css") }}',
                'relative' => '{{ relative_path("/dir1/foo.css") }}',
            ],
        ]);

        self::assertEquals('http://localhost/dir1/dir2/foo.css', $app['twig']->render('absolute'));
        self::assertEquals('../foo.css', $app['twig']->render('relative'));
    }

    public function testAssetIntegration(): void
    {
        $app = new Application();
        $app->register(new TwigServiceProvider(), [
            'twig.templates' => ['hello' => '{{ asset("/foo.css") }}'],
        ]);
        $app->register(new AssetServiceProvider(), [
            'assets.version' => 1,
        ]);

        self::assertEquals('/foo.css?1', $app['twig']->render('hello'));
    }

    public function testGlobalVariable(): void
    {
        $app = new Application();
        $app['request_stack']->push(Request::create('/?name=Fabien'));

        $app->register(new TwigServiceProvider(), [
            'twig.templates' => ['hello' => '{{ global.request.get("name") }}'],
        ]);

        self::assertEquals('Fabien', $app['twig']->render('hello'));
    }

    public function testFormFactory(): void
    {
        $app = new Application();
        $app->register(new FormServiceProvider());
        $app->register(new CsrfServiceProvider());
        $app->register(new TwigServiceProvider());

        self::assertInstanceOf(Environment::class, $app['twig']);
        self::assertInstanceOf(TwigRendererEngine::class, $app['twig.form.engine']);
        self::assertInstanceOf(FormRenderer::class, $app['twig.form.renderer']);
    }

    public function testFormWithoutCsrf(): void
    {
        $app = new Application();
        $app->register(new FormServiceProvider());
        $app->register(new TwigServiceProvider());

        self::assertInstanceOf('Twig_Environment', $app['twig']);
    }

    public function testFormatParameters(): void
    {
        $app = new Application();

        $timezone = new \DateTimeZone('Europe/Paris');

        $app->register(new TwigServiceProvider(), [
            'twig.date.format' => 'Y-m-d',
            'twig.date.interval_format' => '%h hours',
            'twig.date.timezone' => $timezone,
            'twig.number_format.decimals' => 2,
            'twig.number_format.decimal_point' => ',',
            'twig.number_format.thousands_separator' => ' ',
        ]);

        $twig = $app['twig'];

        self::assertSame(['Y-m-d', '%h hours'], $twig->getExtension(CoreExtension::class)->getDateFormat());
        self::assertSame($timezone, $twig->getExtension(CoreExtension::class)->getTimezone());
        self::assertSame([2, ',', ' '], $twig->getExtension(CoreExtension::class)->getNumberFormat());
    }

    public function testWebLinkIntegration(): void
    {
        $app = new Application();
        $app['request_stack']->push($request = Request::create('/'));
        $app->register(new TwigServiceProvider(), [
            'twig.templates' => [
                'preload' => '{{ preload("/foo.css") }}',
            ],
        ]);

        self::assertEquals('/foo.css', $app['twig']->render('preload'));

        $link = new Link('preload', '/foo.css');
        self::assertEquals([$link], array_values($request->attributes->get('_links')->getLinks()));
    }
}
