<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/mail.
 *
 * @link     https://github.com/hyperf-ext/mail
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/mail/blob/master/LICENSE
 */
namespace HyperfExt\Mail\Transport;

use Swift_Events_EventListener;
use Swift_Mime_SimpleMessage;
use Swift_SmtpTransport;

class SmtpTransport extends Transport
{
    /**
     * @var \Swift_SmtpTransport
     */
    protected $transport;

    public function __construct(array $options)
    {
        // The Swift SMTP transport instance will allow us to use any SMTP backend
        // for delivering mail such as Sendgrid, Amazon SES, or a custom server
        // a developer has available. We will just pass this configured host.
        $transport = new Swift_SmtpTransport(
            $options['host'],
            $options['port']
        );

        if (! empty($options['encryption'])) {
            $transport->setEncryption($options['encryption']);
        }

        // Once we have the transport we will check for the presence of a username
        // and password. If we have it we will set the credentials on the Swift
        // transporter instance so that we'll properly authenticate delivery.
        if (isset($options['username'])) {
            $transport->setUsername($options['username']);

            $transport->setPassword($options['password']);
        }

        if (isset($config['stream'])) {
            $transport->setStreamOptions($config['stream']);
        }

        if (isset($config['source_ip'])) {
            $transport->setSourceIp($config['source_ip']);
        }

        if (isset($config['local_domain'])) {
            $transport->setLocalDomain($config['local_domain']);
        }

        if (isset($config['timeout'])) {
            $transport->setTimeout($config['timeout']);
        }

        if (isset($config['auth_mode'])) {
            $transport->setAuthMode($config['auth_mode']);
        }

        $this->transport = $transport;
    }

    public function isStarted()
    {
        return call_user_func_array([$this->transport, __FUNCTION__], func_get_args());
    }

    public function start()
    {
        return call_user_func_array([$this->transport, __FUNCTION__], func_get_args());
    }

    public function stop()
    {
        return call_user_func_array([$this->transport, __FUNCTION__], func_get_args());
    }

    public function ping()
    {
        return call_user_func_array([$this->transport, __FUNCTION__], func_get_args());
    }

    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        return call_user_func_array([$this->transport, __FUNCTION__], func_get_args());
    }

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $count = $this->transport->send($message, $failedRecipients);

        $this->sendPerformed($message);

        return $count;
    }
}
