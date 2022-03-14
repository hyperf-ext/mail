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

use Hyperf\Utils\ApplicationContext;
use HyperfExt\Mail\Contracts\MailableInterface;
use HyperfExt\Mail\Events\MailMessageSending;
use HyperfExt\Mail\Events\MailMessageSent;
use HyperfExt\Mail\Mailer;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mime\Email;

/**
 * @internal
 * @coversNothing
 */
class MailMailerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testGlobalFromIsRespectedOnAllMessages()
    {
        unset($_SERVER['__mailer.test']);
        $mailable = m::mock(MailableInterface::class);
        $mailable->shouldReceive('handler')->once()->andReturn(null);
        $events = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(MailMessageSending::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(MailMessageSent::class));
        $mailer = $this->getMailer();
        $this->setSwiftMailer($mailer);
        $mailer->setAlwaysFrom('eric@zhu.email', 'Taylor Otwell');
        $mailer->getSymfonyMailer()->shouldReceive('send')->once()->with(m::type(Email::class), null)->andReturnUsing(function ($message) {
            $this->assertEquals(['eric@zhu.email' => 'Taylor Otwell'], $message->getFrom());
        });
        $mailer->sendNow($mailable);
    }

    public function testGlobalReturnPathIsRespectedOnAllMessages()
    {
        unset($_SERVER['__mailer.test']);
        $mailable = m::mock(MailableInterface::class);
        $mailable->shouldReceive('handler')->once()->andReturn(null);
        $events = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(MailMessageSending::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(MailMessageSent::class));
        $mailer = $this->getMailer();
        $this->setSwiftMailer($mailer);
        $mailer->setAlwaysReturnPath('eric@zhu.email');
        $mailer->getSymfonyMailer()->shouldReceive('send')->once()->with(m::type(Email::class), null)->andReturnUsing(function ($message) {
            $this->assertSame('eric@zhu.email', $message->getReturnPath());
        });
        $mailer->sendNow($mailable);
    }

    public function testEventsAreDispatched()
    {
        unset($_SERVER['__mailer.test']);

        $mailable = m::mock(MailableInterface::class);
        $mailable->shouldReceive('handler')->once()->andReturn(null);
        $events = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(MailMessageSending::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(MailMessageSent::class));
        $mailer = $this->getMailer($events);
        $this->setSwiftMailer($mailer);
        $mailer->getSymfonyMailer()->shouldReceive('send')->once()->with(m::type(Email::class), null);
    }

    public function testMacroable()
    {
        Mailer::macro('foo', function () {
            return 'bar';
        });

        $mailer = $this->getMailer();

        $this->assertSame(
            'bar',
            $mailer->foo()
        );
    }

    public function setSwiftMailer($mailer)
    {
        $symfonyMailer = m::mock(SymfonyMailer::class);
        $mailer->setSwiftMailer($symfonyMailer);

        return $mailer;
    }

    protected function getMailer($events = null): Mailer
    {
        return new Mailer('smtp', m::mock(SymfonyMailer::class), ApplicationContext::getContainer());
    }

    protected function getMocks(): array
    {
        return ['smtp', m::mock(SymfonyMailer::class), ApplicationContext::getContainer()];
    }
}
