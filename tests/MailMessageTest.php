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
use Swift_Attachment;
use Swift_Message;

/**
 * @internal
 * @coversNothing
 */
class MailMessageTest extends TestCase
{
    /**
     * @var \Mockery::mock
     */
    protected $swift;

    /**
     * @var \HyperfExt\Mail\Message
     */
    protected $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->swift = m::mock(Swift_Message::class);
        $this->message = new Message($this->swift);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testFromMethod()
    {
        $this->swift->shouldReceive('setFrom')->once()->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->setFrom('foo@bar.baz', 'Foo'));
    }

    public function testSenderMethod()
    {
        $this->swift->shouldReceive('setSender')->once()->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->setSender('foo@bar.baz', 'Foo'));
    }

    public function testReturnPathMethod()
    {
        $this->swift->shouldReceive('setReturnPath')->once()->with('foo@bar.baz');
        $this->assertInstanceOf(Message::class, $this->message->setReturnPath('foo@bar.baz'));
    }

    public function testToMethod()
    {
        $this->swift->shouldReceive('addTo')->once()->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->setTo('foo@bar.baz', 'Foo'));
    }

    public function testToMethodWithOverride()
    {
        $this->swift->shouldReceive('addTo')->once()->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->setTo([['address' => 'foo@bar.baz', 'name' => 'Foo']]));
    }

    public function testCcMethod()
    {
        $this->swift->shouldReceive('addCc')->once()->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->setCc('foo@bar.baz', 'Foo'));
    }

    public function testBccMethod()
    {
        $this->swift->shouldReceive('addBcc')->once()->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->setBcc('foo@bar.baz', 'Foo'));
    }

    public function testReplyToMethod()
    {
        $this->swift->shouldReceive('addReplyTo')->once()->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->setReplyTo('foo@bar.baz', 'Foo'));
    }

    public function testSubjectMethod()
    {
        $this->swift->shouldReceive('setSubject')->once()->with('foo');
        $this->assertInstanceOf(Message::class, $this->message->setSubject('foo'));
    }

    public function testPriorityMethod()
    {
        $this->swift->shouldReceive('setPriority')->once()->with(1);
        $this->assertInstanceOf(Message::class, $this->message->setPriority(1));
    }

    public function testGetSwiftMessageMethod()
    {
        $this->assertInstanceOf(Swift_Message::class, $this->message->getSwiftMessage());
    }

    public function testBasicAttachment()
    {
        $swift = m::mock(Swift_Message::class);
        $message = $this->getMockBuilder(Message::class)->setMethods(['createAttachmentFromPath'])->setConstructorArgs([$swift])->getMock();
        $attachment = m::mock(Swift_Attachment::class);
        $message->expects($this->once())->method('createAttachmentFromPath')->with($this->equalTo('foo.jpg'))->willReturn($attachment);
        $swift->shouldReceive('attach')->once()->with($attachment);
        $attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
        $attachment->shouldReceive('setFilename')->once()->with('bar.jpg');
        $message->attach('foo.jpg', ['mime' => 'image/jpeg', 'as' => 'bar.jpg']);
    }

    public function testDataAttachment()
    {
        $swift = m::mock(Swift_Message::class);
        $message = $this->getMockBuilder(Message::class)->setMethods(['createAttachmentFromData'])->setConstructorArgs([$swift])->getMock();
        $attachment = m::mock(Swift_Attachment::class);
        $message->expects($this->once())->method('createAttachmentFromData')->with($this->equalTo('foo'), $this->equalTo('name'))->willReturn($attachment);
        $swift->shouldReceive('attach')->once()->with($attachment);
        $attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
        $message->attachData('foo', 'name', ['mime' => 'image/jpeg']);
    }
}
