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

use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\ApplicationContext;
use HyperfExt\Mail\Contracts\MailableInterface;
use HyperfExt\Mail\Contracts\MailManagerInterface;

class QueuedMailableJob extends Job
{
    /**
     * @var \HyperfExt\Mail\Contracts\MailableInterface
     */
    public $mailable;

    public function __construct(MailableInterface $mailable)
    {
        $this->mailable = $mailable;
    }

    public function handle()
    {
        $this->mailable->send(ApplicationContext::getContainer()->get(MailManagerInterface::class));
    }
}
