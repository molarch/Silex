<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex\Application;

use Symfony\Component\Mime\Email;
/**
 * Mailer trait.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
trait MailerTrait
{
    /**
     * Sends an email.
     *
     * @param Email $message A \Email instance
     * @param array          $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent messages
     */
    public function mail(Email $message, &$failedRecipients = null)
    {
        return $this['mailer']->send($message, $failedRecipients);
    }
}
