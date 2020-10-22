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

interface MailableInterface
{
    /**
     * Set the sender of the message.
     *
     * @param \HyperfExt\Contract\HasMailAddress|string $address
     *
     * @return $this
     */
    public function from($address, ?string $name = null);

    /**
     * Set the "reply to" address of the message.
     *
     * @param \HyperfExt\Contract\HasMailAddress|string $address
     *
     * @return $this
     */
    public function replyTo($address, ?string $name = null);

    /**
     * Add the recipient of the message.
     *
     * @param array|\Hyperf\Utils\Collection|\HyperfExt\Contract\HasMailAddress|string $address
     *
     * @return $this
     */
    public function cc($address, ?string $name = null);

    /**
     * Determine if the given recipient is set on the mailable.
     *
     * @param \HyperfExt\Contract\HasMailAddress|string $address
     */
    public function hasCc($address, ?string $name = null): bool;

    /**
     * Add the recipients of the message.
     *
     * @param array|\Hyperf\Utils\Collection|\HyperfExt\Contract\HasMailAddress|string $address
     *
     * @return $this
     */
    public function bcc($address, ?string $name = null);

    /**
     * Determine if the given recipient is set on the mailable.
     *
     * @param \HyperfExt\Contract\HasMailAddress|string $address
     */
    public function hasBcc($address, ?string $name = null): bool;

    /**
     * Add the recipients of the message.
     *
     * @param array|\Hyperf\Utils\Collection|\HyperfExt\Contract\HasMailAddress|string $address
     *
     * @return $this
     */
    public function to($address, ?string $name = null);

    /**
     * Determine if the given recipient is set on the mailable.
     *
     * @param \HyperfExt\Contract\HasMailAddress|string $address
     */
    public function hasTo($address, ?string $name = null): bool;

    /**
     * Set the subject of the message.
     *
     * @return $this
     */
    public function subject(string $subject);

    /**
     * Set the priority of this message.
     *
     * The value is an integer where 1 is the highest priority and 5 is the lowest.
     *
     * @return $this
     */
    public function priority(int $level);

    /**
     * Set the locale of the message.
     *
     * @return $this
     */
    public function locale(string $locale);

    /**
     * Set the name of the mailer that should be used to send the message.
     *
     * @return $this
     */
    public function mailer(string $mailer);

    /**
     * Set the html view template for the message.
     *
     * @return $this
     */
    public function htmlView(string $template);

    /**
     * Set the plain text view template for the message.
     *
     * @return $this
     */
    public function textView(string $template);

    /**
     * Set the view data for the message.
     *
     * @param array|string $key
     * @param null|mixed $value
     * @return $this
     */
    public function with($key, $value = null);

    /**
     * Set the rendered HTML content for the message.
     *
     * @return $this
     */
    public function htmlBody(string $content);

    /**
     * Set the rendered plain text content for the message.
     *
     * @return $this
     */
    public function textBody(string $content);

    /**
     * Attach a file to the message.
     *
     * @return $this
     */
    public function attach(string $file, array $options = []);

    /**
     * Attach a file to the message from storage.
     *
     * @return $this
     */
    public function attachFromStorage(?string $adapter, string $path, ?string $name = null, array $options = []);

    /**
     * Attach in-memory data as an attachment.
     *
     * @return $this
     */
    public function attachData(string $data, string $name, array $options = []);

    /**
     * Render the message as a view.
     *
     * @param null|\HyperfExt\Mail\Contracts\MailerInterface|\HyperfExt\Mail\Contracts\MailManagerInterface $mailer
     */
    public function render($mailer = null): string;

    /**
     * Send the message using the given mailer and return failed recipients.
     *
     * @param null|\HyperfExt\Mail\Contracts\MailerInterface|\HyperfExt\Mail\Contracts\MailManagerInterface $mailer
     */
    public function send($mailer = null): array;

    /**
     * Queue the message for sending.
     */
    public function queue(?string $queue = null): bool;

    /**
     * Deliver the queued message after the given delay.
     */
    public function later(int $delay, ?string $queue = null): bool;
}
