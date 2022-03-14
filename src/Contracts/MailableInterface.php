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

use Hyperf\Utils\Collection;
use HyperfExt\Contract\HasMailAddress;

interface MailableInterface
{
    /**
     * Set the sender of the message.
     */
    public function from(string|HasMailAddress $address, ?string $name = null): self;

    /**
     * Set the "reply to" address of the message.
     */
    public function replyTo(string|HasMailAddress $address, ?string $name = null): self;

    /**
     * Add the recipient of the message.
     *
     * @param Collection|HasMailAddress|HasMailAddress[]|string|string[] $address
     */
    public function cc(array|Collection|HasMailAddress|string $address, ?string $name = null): self;

    /**
     * Determine if the given recipient is set on the mailable.
     */
    public function hasCc(string|HasMailAddress $address, ?string $name = null): bool;

    /**
     * Add the recipients of the message.
     *
     * @param Collection|HasMailAddress|HasMailAddress[]|string|string[] $address
     */
    public function bcc(array|Collection|HasMailAddress|string $address, ?string $name = null): self;

    /**
     * Determine if the given recipient is set on the mailable.
     */
    public function hasBcc(string|HasMailAddress $address, ?string $name = null): bool;

    /**
     * Add the recipients of the message.
     *
     * @param Collection|HasMailAddress|HasMailAddress[]|string|string[] $address
     */
    public function to(array|Collection|HasMailAddress|string $address, ?string $name = null): self;

    /**
     * Determine if the given recipient is set on the mailable.
     */
    public function hasTo(string|HasMailAddress $address, ?string $name = null): bool;

    /**
     * Set the subject of the message.
     */
    public function subject(string $subject): self;

    /**
     * Set the priority of this message.
     *
     * The value is an integer where 1 is the highest priority and 5 is the lowest.
     */
    public function priority(int $level): self;

    /**
     * Set the locale of the message.
     */
    public function locale(string $locale): self;

    /**
     * Set the name of the mailer that should be used to send the message.
     */
    public function mailer(string $mailer): self;

    /**
     * Set the html view template for the message.
     */
    public function htmlView(string $template): self;

    /**
     * Set the plain text view template for the message.
     */
    public function textView(string $template): self;

    /**
     * Set the view data for the message.
     */
    public function with(array|string $key, mixed $value = null): self;

    /**
     * Set the rendered HTML content for the message.
     */
    public function htmlBody(string $content): self;

    /**
     * Set the rendered plain text content for the message.
     */
    public function textBody(string $content): self;

    /**
     * Attach a file to the message.
     */
    public function attach(string $file, array $options = []): self;

    /**
     * Attach a file to the message from storage.
     */
    public function attachFromStorage(?string $adapter, string $path, ?string $name = null, array $options = []): self;

    /**
     * Attach in-memory data as an attachment.
     */
    public function attachData(string $data, string $name, array $options = []): self;

    /**
     * Render the message as a view.
     */
    public function render(null|MailerInterface|MailManagerInterface $mailer = null): string;

    /**
     * Send the message using the given mailer.
     */
    public function send(null|MailerInterface|MailManagerInterface $mailer = null): void;

    /**
     * Queue the message for sending.
     */
    public function queue(?string $queue = null): bool;

    /**
     * Deliver the queued message after the given delay.
     */
    public function later(int $delay, ?string $queue = null): bool;
}
