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

interface MailManagerInterface
{
    /**
     * Get a mailer instance by name.
     */
    public function get(string $name): MailerInterface;
}
