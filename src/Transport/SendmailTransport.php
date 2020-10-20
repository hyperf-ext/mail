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
use Swift_SendmailTransport;

class SendmailTransport extends Transport
{
    /**
     * @var \Swift_SendmailTransport
     */
    protected $transport;

    public function __construct(array $options)
    {
        $this->transport = new Swift_SendmailTransport(
            $options['path'] ?? '/usr/sbin/sendmail -bs'
        );
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
