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

use Hyperf\Utils\ApplicationContext;
use HyperfExt\Contract\HasLocalePreference;
use HyperfExt\Mail\Contracts\MailableInterface;
use HyperfExt\Mail\Contracts\MailManagerInterface;

class PendingMail
{
    /**
     * The mailer instance.
     *
     * @var \HyperfExt\Mail\Mailer|\HyperfExt\Mail\MailManager
     */
    protected $mailer;

    /**
     * The locale of the message.
     *
     * @var string
     */
    protected $locale;

    /**
     * The "to" recipients of the message.
     *
     * @var array
     */
    protected $to = [];

    /**
     * The "cc" recipients of the message.
     *
     * @var array
     */
    protected $cc = [];

    /**
     * The "bcc" recipients of the message.
     *
     * @var array
     */
    protected $bcc = [];

    /**
     * Create a new mailable mailer instance.
     *
     * @param \HyperfExt\Mail\Contracts\MailerInterface|\HyperfExt\Mail\Contracts\MailManagerInterface $mailer
     */
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Set the locale of the message.
     *
     * @return $this
     */
    public function locale(string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * @param mixed $users
     * @return $this
     */
    public function to($users)
    {
        $this->to = $users;

        if (! $this->locale &&
            is_object($users) &&
            $users instanceof HasLocalePreference &&
            is_string($locale = $users->getPreferredLocale())
        ) {
            $this->locale($locale);
        }

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * @param mixed $users
     * @return $this
     */
    public function cc($users)
    {
        $this->cc = $users;

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * @param mixed $users
     * @return $this
     */
    public function bcc($users)
    {
        $this->bcc = $users;

        return $this;
    }

    /**
     * Set the mailer of the message.
     *
     * @return $this
     */
    public function mailer(string $name)
    {
        $this->mailer = ApplicationContext::getContainer()->get(MailManagerInterface::class)->get($name);

        return $this;
    }

    /**
     * Render the given message as a view.
     */
    public function render(MailableInterface $mailable): string
    {
        return $this->mailer->render($this->fill($mailable));
    }

    /**
     * Send a new mailable message instance.
     */
    public function send(MailableInterface $mailable): array
    {
        return $this->mailer->send($this->fill($mailable));
    }

    /**
     * Push the given mailable onto the queue.
     */
    public function queue(MailableInterface $mailable, ?string $queue = null): bool
    {
        return $this->mailer->queue($this->fill($mailable), $queue);
    }

    /**
     * Deliver the queued message after the given delay.
     */
    public function later(MailableInterface $mailable, int $delay, ?string $queue = null): bool
    {
        return $this->mailer->later($this->fill($mailable), $delay, $queue);
    }

    /**
     * Populate the mailable with the addresses.
     */
    protected function fill(MailableInterface $mailable): MailableInterface
    {
        return tap($mailable->to($this->to)
            ->cc($this->cc)
            ->bcc($this->bcc), function (MailableInterface $mailable) {
                if ($this->locale) {
                    $mailable->locale($this->locale);
                }
            });
    }
}
