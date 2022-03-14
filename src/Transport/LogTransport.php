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

use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class LogTransport implements TransportInterface
{
    /**
     * The Logger instance.
     */
    protected \Psr\Log\LoggerInterface $logger;

    /**
     * Create a new log transport instance.
     */
    public function __construct(ContainerInterface $container, array $options = [])
    {
        $this->logger = $container->get(LoggerFactory::class)->get(
            $options['name'] ?? 'mail.local',
            $options['group'] ?? 'default'
        );
    }

    public function __toString(): string
    {
        return 'log://';
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        if ($message instanceof Email) {
            $this->logger->debug($this->getMimeEntityString($message));
        }

        return $message;
    }

    /**
     * Get the logger for the LogTransport instance.
     */
    public function logger(): \Psr\Log\LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get a loggable string out of a Email entity.
     */
    protected function getMimeEntityString(Email $entity): string
    {
        return $entity->toString();
    }
}
