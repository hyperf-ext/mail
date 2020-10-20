<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/mail.
 *
 * @link     https://github.com/hyperf-ext/mail
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/mail/blob/master/LICENSE
 */
namespace HyperfExt\Mail;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Swift_Events_CommandEvent;
use Swift_Events_CommandListener;
use Swift_Events_ResponseEvent;
use Swift_Events_ResponseListener;
use Swift_Events_SendEvent;
use Swift_Events_SendListener;
use Swift_Events_TransportChangeEvent;
use Swift_Events_TransportChangeListener;
use Swift_Events_TransportExceptionEvent;
use Swift_Events_TransportExceptionListener;
use Swift_TransportException;

class SwiftMailerLoggerPlugin implements Swift_Events_SendListener, Swift_Events_CommandListener, Swift_Events_ResponseListener, Swift_Events_TransportChangeListener, Swift_Events_TransportExceptionListener
{
    /**
     * The PSR-3 logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Map of events to log-levels.
     *
     * @var array
     */
    protected $levels = [
        'sendPerformed.SUCCESS' => LogLevel::INFO,
        'sendPerformed.TENTATIVE' => LogLevel::WARNING,
        'sendPerformed.NOT_SUCCESS' => LogLevel::ERROR,
        'sendPerformed.PENDING' => LogLevel::DEBUG,
        'sendPerformed.SPOOLED' => LogLevel::DEBUG,
        'exceptionThrown' => LogLevel::ERROR,
        'beforeSendPerformed' => LogLevel::DEBUG,
        'commandSent' => LogLevel::DEBUG,
        'responseReceived' => LogLevel::DEBUG,
        'beforeTransportStarted' => LogLevel::DEBUG,
        'transportStarted' => LogLevel::DEBUG,
        'beforeTransportStopped' => LogLevel::DEBUG,
        'transportStopped' => LogLevel::DEBUG,
    ];

    public function __construct(LoggerInterface $logger, array $levels = [])
    {
        $this->logger = $logger;
        foreach ($levels as $event => $level) {
            $this->levels[$event] = $level;
        }
    }

    /**
     * Invoked immediately before the Message is sent.
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $event)
    {
        $level = $this->levels['beforeSendPerformed'];
        $this->log($level, 'MESSAGE (beforeSend): ', [
            'message' => $event->getMessage()->toString(),
        ]);
    }

    /**
     * Invoked immediately after the Message is sent.
     */
    public function sendPerformed(Swift_Events_SendEvent $event)
    {
        $result = $event->getResult();
        $failed_recipients = $event->getFailedRecipients();
        $message = $event->getMessage();

        switch ($result) {
            case Swift_Events_SendEvent::RESULT_PENDING:
                $level = $this->levels['sendPerformed.PENDING'];
                break;
            case Swift_Events_SendEvent::RESULT_SPOOLED:
                $level = $this->levels['sendPerformed.SPOOLED'];
                break;
            case Swift_Events_SendEvent::RESULT_TENTATIVE:
                $level = $this->levels['sendPerformed.TENTATIVE'];
                break;
            case Swift_Events_SendEvent::RESULT_SUCCESS:
                $level = $this->levels['sendPerformed.SUCCESS'];
                break;
            default:
                $level = $this->levels['sendPerformed.NOT_SUCCESS'];
                break;
        }

        $this->log($level, 'MESSAGE (sendPerformed): ', [
            'result' => $result,
            'failed_recipients' => $failed_recipients,
            'message' => $message->toString(),
        ]);
    }

    /**
     * Invoked immediately following a command being sent.
     */
    public function commandSent(Swift_Events_CommandEvent $event)
    {
        $level = $this->levels['commandSent'];
        $command = $event->getCommand();
        $this->log($level, sprintf('>> %s', $command));
    }

    /**
     * Invoked immediately following a response coming back.
     */
    public function responseReceived(Swift_Events_ResponseEvent $event)
    {
        $level = $this->levels['responseReceived'];
        $response = $event->getResponse();
        $this->log($level, sprintf('<< %s', $response));
    }

    /**
     * Invoked just before a Transport is started.
     */
    public function beforeTransportStarted(Swift_Events_TransportChangeEvent $event)
    {
        $level = $this->levels['beforeTransportStarted'];
        $transportName = get_class($event->getSource());
        $this->log($level, sprintf('++ Starting %s', $transportName));
    }

    /**
     * Invoked immediately after the Transport is started.
     */
    public function transportStarted(Swift_Events_TransportChangeEvent $event)
    {
        $level = $this->levels['transportStarted'];
        $transportName = get_class($event->getSource());
        $this->log($level, sprintf('++ %s started', $transportName));
    }

    /**
     * Invoked just before a Transport is stopped.
     */
    public function beforeTransportStopped(Swift_Events_TransportChangeEvent $event)
    {
        $level = $this->levels['beforeTransportStopped'];
        $transportName = get_class($event->getSource());
        $this->log($level, sprintf('++ Stopping %s', $transportName));
    }

    /**
     * Invoked immediately after the Transport is stopped.
     */
    public function transportStopped(Swift_Events_TransportChangeEvent $event)
    {
        $level = $this->levels['transportStopped'];
        $transportName = get_class($event->getSource());
        $this->log($level, sprintf('++ %s stopped', $transportName));
    }

    /**
     * Invoked as a TransportException is thrown in the Transport system.
     *
     * @throws \Swift_TransportException
     */
    public function exceptionThrown(Swift_Events_TransportExceptionEvent $event)
    {
        $e = $event->getException();
        $message = $e->getMessage();

        $level = $this->levels['exceptionThrown'];
        $this->log($level, sprintf('!! %s', $message));

        $event->cancelBubble();
        throw new Swift_TransportException($message);
    }

    /**
     * Adds the message and invokes the logger->log() method.
     *
     * @param mixed $level
     */
    protected function log($level, string $message, array $context = [])
    {
        // Using a falsy level disables logging
        if ($level) {
            $this->logger->log($level, $message, $context);
        }
    }
}
