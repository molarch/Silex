<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests;

use Silex\Exception\ControllerFrozenException;
use PHPUnit\Framework\TestCase;
use Silex\Controller;
use Silex\Route;

/**
 * Controller test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ControllerTest extends TestCase
{
    public function testBind()
    {
        $controller = new Controller(new Route('/foo'));
        $ret = $controller->bind('foo');

        $this->assertSame($ret, $controller);
        $this->assertEquals('foo', $controller->getRouteName());
    }

    public function testBindOnFrozenControllerShouldThrowException()
    {
        $this->expectException(ControllerFrozenException::class);
        $controller = new Controller(new Route('/foo'));
        $controller->bind('foo');
        $controller->freeze();
        $controller->bind('bar');
    }

    public function testAssert()
    {
        $controller = new Controller(new Route('/foo/{bar}'));
        $ret = $controller->assert('bar', '\d+');

        $this->assertSame($ret, $controller);
        $this->assertEquals(['bar' => '\d+'], $controller->getRoute()->getRequirements());
    }

    public function testValue()
    {
        $controller = new Controller(new Route('/foo/{bar}'));
        $ret = $controller->value('bar', 'foo');

        $this->assertSame($ret, $controller);
        $this->assertEquals(['bar' => 'foo'], $controller->getRoute()->getDefaults());
    }

    public function testConvert()
    {
        $controller = new Controller(new Route('/foo/{bar}'));
        $ret = $controller->convert('bar', $func = function ($bar) { return $bar; });

        $this->assertSame($ret, $controller);
        $this->assertEquals(['bar' => $func], $controller->getRoute()->getOption('_converters'));
    }

    public function testRun()
    {
        $controller = new Controller(new Route('/foo/{bar}'));
        $ret = $controller->run($cb = function () { return 'foo'; });

        $this->assertSame($ret, $controller);
        $this->assertEquals($cb, $controller->getRoute()->getDefault('_controller'));
    }

    /**
     * @dataProvider provideRouteAndExpectedRouteName
     */
    public function testDefaultRouteNameGeneration(Route $route, $prefix, $expectedRouteName)
    {
        $controller = new Controller($route);
        $controller->bind($controller->generateRouteName($prefix));

        $this->assertEquals($expectedRouteName, $controller->getRouteName());
    }

    public function provideRouteAndExpectedRouteName()
    {
        return [
            [new Route('/Invalid%Symbols#Stripped', [], [], [], '', [], ['POST']), '', 'POST_InvalidSymbolsStripped'],
            [new Route('/post/{id}', [], [], [], '', [], ['GET']), '', 'GET_post_id'],
            [new Route('/colon:pipe|dashes-escaped'), '', '_colon_pipe_dashes_escaped'],
            [new Route('/underscores_and.periods'), '', '_underscores_and.periods'],
            [new Route('/post/{id}', [], [], [], '', [], ['GET']), 'prefix', 'GET_prefix_post_id'],
        ];
    }

    public function testRouteExtension()
    {
        $route = new MyRoute();

        $controller = new Controller($route);
        $controller->foo('foo');

        $this->assertEquals('foo', $route->foo);
    }

    public function testRouteMethodDoesNotExist()
    {
        $this->expectException(\BadMethodCallException::class);
        $route = new MyRoute();

        $controller = new Controller($route);
        $controller->bar();
    }
}

class MyRoute extends Route
{
    public $foo;

    public function foo($value)
    {
        $this->foo = $value;
    }
}
