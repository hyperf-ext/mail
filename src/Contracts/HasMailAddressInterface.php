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

interface HasMailAddressInterface
{
    /**
     * Get the mail address of the entity.
     */
    public function getMailAddress(): string;

    /**
     * Get the mail address display name of the entity.
     */
    public function getMailAddressDisplayName(): ?string;
}
