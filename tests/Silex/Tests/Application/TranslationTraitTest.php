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
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Translator;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TranslationTraitTest extends TestCase
{
    public function testTrans(): void
    {
        $app = $this->createApplication();
        $app['translator'] = $translator = $this->getMockBuilder(Translator::class)->disableOriginalConstructor()->getMock();
        $translator->expects(self::once())->method('trans');
        $app->trans('foo');
    }

    public function createApplication(): TranslationApplication
    {
        $app = new TranslationApplication();
        $app->register(new TranslationServiceProvider());

        return $app;
    }
}
