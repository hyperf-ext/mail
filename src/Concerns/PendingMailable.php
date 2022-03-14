<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/mail.
 *
 * @link     https://github.com/hyperf-ext/mail
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/mail/blob/master/LICENSE
 */
namespace HyperfExt\Mail\Concerns;

use Hyperf\Utils\Collection;
use HyperfExt\Contract\HasMailAddress;
use HyperfExt\Mail\PendingMail;

trait PendingMailable
{
    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param Collection|HasMailAddress|HasMailAddress[]|string|string[] $users
     */
    public function to(array|Collection|HasMailAddress|string $users): PendingMail
    {
        return (new PendingMail($this))->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param Collection|HasMailAddress|HasMailAddress[]|string|string[] $users
     */
    public function cc(array|Collection|HasMailAddress $users): PendingMail
    {
        return (new PendingMail($this))->cc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param Collection|HasMailAddress|HasMailAddress[]|string|string[] $users
     */
    public function bcc(array|Collection|HasMailAddress $users): PendingMail
    {
        return (new PendingMail($this))->bcc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function locale(string $locale): PendingMail
    {
        return (new PendingMail($this))->locale($locale);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function mailer(string $name): PendingMail
    {
        return (new PendingMail($this))->mailer($name);
    }
}
