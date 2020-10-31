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
use Swift_Mailer;
use Swift_Message;
use Swift_Transport;

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
        $mailer->getSwiftMailer()->shouldReceive('send')->once()->with(m::type(Swift_Message::class), [])->andReturnUsing(function ($message) {
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
        $mailer->getSwiftMailer()->shouldReceive('send')->once()->with(m::type(Swift_Message::class), [])->andReturnUsing(function ($message) {
            $this->assertSame('eric@zhu.email', $message->getReturnPath());
        });
        $mailer->sendNow($mailable);
    }

    public function testFailedRecipientsAreAppendedAndCanBeRetrieved()
    {
        unset($_SERVER['__mailer.test']);
        $mailable = m::mock(MailableInterface::class);
        $mailable->shouldReceive('handler')->once()->andReturn(null);
        $events = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(MailMessageSending::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(MailMessageSent::class));
        $mailer = $this->getMailer();
        $mailer->getSwiftMailer()->shouldReceive('getTransport')->andReturn($transport = m::mock(Swift_Transport::class));
        $transport->shouldReceive('stop');
        $swift = new FailingSwiftMailerStub($transport);
        $mailer->setSwiftMailer($swift);

        $failures = $mailer->sendNow($mailable);

        $this->assertEquals(['eric@zhu.email'], $failures);
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
        $mailer->getSwiftMailer()->shouldReceive('send')->once()->with(m::type(Swift_Message::class), []);

        self::assertNull($mailer->sendNow($mailable));
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
        $swift = m::mock(Swift_Mailer::class);
        $swift->shouldReceive('createMessage')->andReturn(new Swift_Message());
        $swift->shouldReceive('getTransport')->andReturn($transport = m::mock(Swift_Transport::class));
        $transport->shouldReceive('stop');
        $mailer->setSwiftMailer($swift);

        return $mailer;
    }

    protected function getMailer($events = null)
    {
        return new Mailer('smtp', m::mock(Swift_Mailer::class), ApplicationContext::getContainer());
    }

    protected function getMocks()
    {
        return ['smtp', m::mock(Swift_Mailer::class), ApplicationContext::getContainer()];
    }
}

class FailingSwiftMailerStub extends Swift_Mailer
{
    public function send($message, &$failed = null)
    {
        $failed[] = 'eric@zhu.email';
    }

    public function getTransport()
    {
        $transport = m::mock(Swift_Transport::class);
        $transport->shouldReceive('stop');

        return $transport;
    }

    public function createMessage($service = 'message')
    {
        return new Swift_Message();
    }
}
