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

use HyperfExt\Mail\Message;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

/**
 * @internal
 * @coversNothing
 */
class MailMessageTest extends TestCase
{
    /**
     * @var \Mockery::mock
     */
    protected $email;

    protected Message $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->email = m::mock(Email::class);
        $this->message = new Message($this->email);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testFromMethod()
    {
        $this->email->shouldReceive('from')->once()->with(new Address('foo@bar.baz', 'Foo'));
        $this->assertInstanceOf(Message::class, $this->message->setFrom('foo@bar.baz', 'Foo'));
    }

    public function testSenderMethod()
    {
        $this->email->shouldReceive('sender')->once()->with(new Address('foo@bar.baz', 'Foo'));
        $this->assertInstanceOf(Message::class, $this->message->setSender('foo@bar.baz', 'Foo'));
    }

    public function testReturnPathMethod()
    {
        $this->email->shouldReceive('returnPath')->once()->with('foo@bar.baz');
        $this->assertInstanceOf(Message::class, $this->message->setReturnPath('foo@bar.baz'));
    }

    public function testToMethod()
    {
        $this->email->shouldReceive('to')->once()->with(new Address('foo@bar.baz', 'Foo'));
        $this->assertInstanceOf(Message::class, $this->message->setTo('foo@bar.baz', 'Foo'));
    }

    public function testToMethodWithOverride()
    {
        $this->email->shouldReceive('to')->once()->with(new Address('foo@bar.baz', 'Foo'));
        $this->assertInstanceOf(Message::class, $this->message->setTo([['address' => 'foo@bar.baz', 'name' => 'Foo']]));
    }

    public function testCcMethod()
    {
        $this->email->shouldReceive('cc')->once()->with(new Address('foo@bar.baz', 'Foo'));
        $this->assertInstanceOf(Message::class, $this->message->setCc('foo@bar.baz', 'Foo'));
    }

    public function testBccMethod()
    {
        $this->email->shouldReceive('bcc')->once()->with(new Address('foo@bar.baz', 'Foo'));
        $this->assertInstanceOf(Message::class, $this->message->setBcc('foo@bar.baz', 'Foo'));
    }

    public function testReplyToMethod()
    {
        $this->email->shouldReceive('replyTo')->once()->with(new Address('foo@bar.baz', 'Foo'));
        $this->assertInstanceOf(Message::class, $this->message->setReplyTo('foo@bar.baz', 'Foo'));
    }

    public function testSubjectMethod()
    {
        $this->email->shouldReceive('subject')->once()->with('foo');
        $this->assertInstanceOf(Message::class, $this->message->setSubject('foo'));
    }

    public function testPriorityMethod()
    {
        $this->email->shouldReceive('priority')->once()->with(1);
        $this->assertInstanceOf(Message::class, $this->message->setPriority(1));
    }

    public function testGetSwiftMessageMethod()
    {
        $this->assertInstanceOf(Email::class, $this->message->getEmail());
    }

    public function testBasicAttachment()
    {
        $email = m::mock(Email::class);
        $message = $this->getMockBuilder(Message::class)->onlyMethods(['createAttachmentFromPath'])->setConstructorArgs([$email])->getMock();
        $attachment = m::mock(DataPart::class);
        $message->expects($this->once())->method('createAttachmentFromPath')->with($this->equalTo('foo.jpg'), $this->equalTo(['mime' => 'image/jpeg', 'as' => 'bar.jpg']))->willReturn($attachment);
        $email->shouldReceive('attachPart')->once()->with($attachment);
        $message->attachFile('foo.jpg', ['mime' => 'image/jpeg', 'as' => 'bar.jpg']);
    }

    public function testDataAttachment()
    {
        $email = m::mock(Email::class);
        $message = $this->getMockBuilder(Message::class)->onlyMethods(['createAttachmentFromData'])->setConstructorArgs([$email])->getMock();
        $attachment = m::mock(DataPart::class);
        $message->expects($this->once())->method('createAttachmentFromData')->with($this->equalTo('foo'), $this->equalTo('name'), $this->equalTo(['mime' => 'image/jpeg']))->willReturn($attachment);
        $email->shouldReceive('attachPart')->once()->with($attachment);
        $message->attachData('foo', 'name', ['mime' => 'image/jpeg']);
    }
}
