<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/mail.
 *
 * @link     https://github.com/hyperf-ext/mail
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/mail/blob/master/LICENSE
 */
namespace HyperfExt\Mail\Contracts;

interface MailerInterface
{
    /**
     * Render the given message as a view.
     */
    public function render(MailableInterface $mailable): string;

    /**
     * Send a new message using a mailable instance and return failed recipients.
     *
     * @param callable|\Closure|\HyperfExt\Mail\Contracts\MailableInterface $mailable
     */
    public function sendNow(MailableInterface $mailable): ?array;

    /**
     * Send a new message using a mailable instance and return failed recipients.
     *
     * @param array|callable|\Closure|\HyperfExt\Mail\Contracts\MailableInterface $mailable
     */
    public function send(MailableInterface $mailable);

    /**
     * Queue a new e-mail message for sending.
     */
    public function queue(MailableInterface $mailable, ?string $queue = null): bool;

    /**
     * Queue a new e-mail message for sending.
     */
    public function later(MailableInterface $mailable, int $delay, ?string $queue = null): bool;
}
