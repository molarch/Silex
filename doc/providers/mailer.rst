Mailer
======

The *MailerServiceProvider* provides a service for sending email through
the `Mailer <https://symfony.com/components/Mailer>`_ library.

You can use the ``mailer`` service to send messages easily. By default, it
will attempt to send emails through SMTP.

Parameters
----------

* **mailer.dsn**: A connection sting including options for the Transport.

Services
--------

* **mailer**: The mailer instance.

  Example usage::

    $message = new Email();

    // ...

    $app['mailer']->send($message);

* **mailer.transport**: The transport used for e-mail delivery.

Registering
-----------

.. code-block:: php

    $app->register(new Silex\Provider\MailerServiceProvider());

.. note::

    Add Mailer as a dependency:

    .. code-block:: bash

        composer require symfony/mailer

Usage
-----

The Mailer provider provides a ``mailer`` service::

    use Symfony\Component\HttpFoundation\Request;

    $app->post('/feedback', function (Request $request) use ($app) {
        $message = new Email()
            ->setSubject('[YourSite] Feedback')
            ->setFrom(array('noreply@yoursite.com'))
            ->setTo(array('feedback@yoursite.com'))
            ->setBody($request->get('message'));

        $app['mailer']->send($message);

        return new Response('Thank you for your feedback!', 201);
    });

Traits
------

``Silex\Application\MailerTrait`` adds the following shortcuts:

* **mail**: Sends an email.

.. code-block:: php

    $app->mail(new Email()
        ->setSubject('[YourSite] Feedback')
        ->setFrom(array('noreply@yoursite.com'))
        ->setTo(array('feedback@yoursite.com'))
        ->setBody($request->get('message')));

For more information, check out the `Mailer documentation
<https://symfony.com/doc/current/mailer.html>`_.
