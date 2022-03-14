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

use Hyperf\Macroable\Macroable;
use HyperfExt\Contract\ShouldQueue;
use HyperfExt\Mail\Concerns\PendingMailable;
use HyperfExt\Mail\Contracts\MailableInterface;
use HyperfExt\Mail\Contracts\MailerInterface;
use HyperfExt\Mail\Events\MailMessageSending;
use HyperfExt\Mail\Events\MailMessageSent;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Email;

class Mailer implements MailerInterface
{
    use Macroable;
    use PendingMailable;

    /**
     * The event dispatcher instance.
     */
    protected ?EventDispatcherInterface $eventDispatcher;

    /**
     * The global from address and name.
     */
    protected array $from;

    /**
     * The global reply-to address and name.
     */
    protected array $replyTo;

    /**
     * The global return path address.
     */
    protected array $returnPath;

    /**
     * The global to address and name.
     */
    protected array $to;

    /**
     * Array of failed recipients.
     */
    protected array $failedRecipients = [];

    /**
     * Create a new Mailer instance.
     */
    public function __construct(
        protected string $name,
        protected SymfonyMailerInterface $mailer,
        protected ContainerInterface $container,
    ) {
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
    }

    /**
     * Set the global from address and name.
     */
    public function setAlwaysFrom(string $address, ?string $name = null): self
    {
        $this->from = compact('address', 'name');

        return $this;
    }

    /**
     * Set the global reply-to address and name.
     */
    public function setAlwaysReplyTo(string $address, ?string $name = null): self
    {
        $this->replyTo = compact('address', 'name');

        return $this;
    }

    /**
     * Set the global return path address.
     */
    public function setAlwaysReturnPath(string $address): self
    {
        $this->returnPath = compact('address');

        return $this;
    }

    /**
     * Set the global to address and name.
     */
    public function setAlwaysTo(string $address, ?string $name = null): self
    {
        $this->to = compact('address', 'name');

        return $this;
    }

    public function render(MailableInterface $mailable): string
    {
        $message = $this->createMessage();
        $mailable->handler($message);

        return $message->getBody()->toString();
    }

    public function sendNow(MailableInterface $mailable): void
    {
        $message = $this->createMessage();

        $mailable->handler($message);

        $data = $message->getData();

        // If a global "to" address has been set, we will set that address on the mail
        // message. This is primarily useful during local development in which each
        // message should be delivered into a single mail address for inspection.
        if (isset($this->to['address'])) {
            $this->setGlobalToAndRemoveCcAndBcc($message);
        }

        // Next we will determine if the message should be sent. We give the developer
        // one final chance to stop this message and then we will send it to all of
        // its recipients. We will then fire the sent event for the sent message.
        $email = $message->getEmail();

        $this->eventDispatcher->dispatch(new MailMessageSending($email, $data));

        $this->sendEmail($email);

        $this->eventDispatcher->dispatch(new MailMessageSent($email, $data));
    }

    public function send(MailableInterface $mailable): void
    {
        $mailable instanceof ShouldQueue
            ? $mailable->mailer($this->name)->queue()
            : $mailable->mailer($this->name)->send($this);
    }

    public function queue(MailableInterface $mailable, ?string $queue = null): bool
    {
        return $mailable->mailer($this->name)->queue($queue);
    }

    public function later(MailableInterface $mailable, int $delay, ?string $queue = null): bool
    {
        return $mailable->mailer($this->name)->later($delay, $queue);
    }

    /**
     * Get the Symfony Mailer instance.
     */
    public function getSymfonyMailer(): SymfonyMailerInterface
    {
        return $this->mailer;
    }

    /**
     * Set the Symfony Mailer instance.
     */
    public function setSymfonyMailer(SymfonyMailerInterface $mailer): void
    {
        $this->mailer = $mailer;
    }

    /**
     * Set the global "to" address on the given message.
     */
    protected function setGlobalToAndRemoveCcAndBcc(Message $message): void
    {
        $message->setTo($this->to['address'], $this->to['name']);
        $message->setCc(null, null, true);
        $message->setBcc(null, null, true);
    }

    /**
     * Create a new message instance.
     */
    protected function createMessage(): Message
    {
        $message = new Message(new Email());

        // If a global from address has been specified we will set it on every message
        // instance so the developer does not have to repeat themselves every time
        // they create a new message. We'll just go ahead and push this address.
        if (! empty($this->from['address'])) {
            $message->setFrom($this->from['address'], $this->from['name']);
        }

        // When a global reply address was specified we will set this on every message
        // instance so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push this address.
        if (! empty($this->replyTo['address'])) {
            $message->setReplyTo($this->replyTo['address'], $this->replyTo['name']);
        }

        if (! empty($this->returnPath['address'])) {
            $message->setReturnPath($this->returnPath['address']);
        }

        return $message;
    }

    /**
     * Send a Symfony Email instance.
     */
    protected function sendEmail(Email $message): void
    {
        try {
            $this->mailer->send($message);
        } finally {
            $this->forceReconnection();
        }
    }

    /**
     * Force the transport to re-connect.
     *
     * This will prevent errors in daemon queue situations.
     */
    protected function forceReconnection()
    {
        // TODO check if reconnect is required
    }
}
