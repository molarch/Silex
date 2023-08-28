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
use Silex\Provider\MailerServiceProvider;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MailerTraitTest extends TestCase
{
    public function testMail()
    {
        $app = $this->createApplication();

        $message = $this->getMockBuilder('Message')->disableOriginalConstructor()->getMock();
        $app['mailer'] = $mailer = $this->getMockBuilder('Mailer')->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())
               ->method('send')
               ->with($message)
        ;

        $app->mail($message);
    }

    public function createApplication()
    {
        $app = new MailerApplication();
        $app->register(new MailerServiceProvider());

        return $app;
    }
}
