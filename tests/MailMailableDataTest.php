<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/mail.
 *
 * @link     https://github.com/hyperf-ext/mail
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/mail/blob/master/LICENSE
 */
namespace HyperfTest\Mail;

use HyperfExt\Mail\Mailable;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MailMailableDataTest extends TestCase
{
    public function testMailableDataIsNotLost()
    {
        $testData = ['first_name' => 'James'];

        $mailable = new MailableStub();
        $mailable->build(function ($m) use ($testData) {
            $m->htmlView('view')
                ->with($testData);
        });
        $this->assertSame($testData, $mailable->buildViewData());

        $mailable = new MailableStub();
        $mailable->build(function ($m) use ($testData) {
            $m->htmlView('view')
                ->textView('text-view')
                ->with($testData);
        });
        $this->assertSame($testData, $mailable->buildViewData());
    }
}

class MailableStub extends Mailable
{
    /**
     * Build the message.
     * @param mixed $builder
     */
    public function build($builder)
    {
        $builder($this);
    }
}
