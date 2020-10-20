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

use HyperfExt\Mail\Contracts\HasMailAddressInterface;
use HyperfExt\Mail\Mailable;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MailMailableTest extends TestCase
{
    public function testMailableSetsRecipientsCorrectly()
    {
        $mailable = new WelcomeMailableStub();
        $mailable->to('eric@zhu.email');
        $this->assertEquals([['name' => null, 'address' => 'eric@zhu.email']], $mailable->to);
        $this->assertTrue($mailable->hasTo('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->to('eric@zhu.email', 'Eric Zhu');
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->to);
        $this->assertTrue($mailable->hasTo('eric@zhu.email', 'Eric Zhu'));
        $this->assertTrue($mailable->hasTo('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->to(['eric@zhu.email']);
        $this->assertEquals([['name' => null, 'address' => 'eric@zhu.email']], $mailable->to);
        $this->assertTrue($mailable->hasTo('eric@zhu.email'));
        $this->assertFalse($mailable->hasTo('eric@zhu.email', 'Eric Zhu'));

        $mailable = new WelcomeMailableStub();
        $mailable->to([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']]);
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->to);
        $this->assertTrue($mailable->hasTo('eric@zhu.email', 'Eric Zhu'));
        $this->assertTrue($mailable->hasTo('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->to(new MailableTestUserStub());
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->to);
        $this->assertTrue($mailable->hasTo(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasTo('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->to(collect([new MailableTestUserStub()]));
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->to);
        $this->assertTrue($mailable->hasTo(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasTo('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->to(collect([new MailableTestUserStub(), new MailableTestUserStub()]));
        $this->assertEquals([
            ['name' => 'Eric Zhu', 'address' => 'eric@zhu.email'],
            ['name' => 'Eric Zhu', 'address' => 'eric@zhu.email'],
        ], $mailable->to);
        $this->assertTrue($mailable->hasTo(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasTo('eric@zhu.email'));
    }

    public function testMailableSetsCcRecipientsCorrectly()
    {
        $mailable = new WelcomeMailableStub();
        $mailable->cc('eric@zhu.email');
        $this->assertEquals([['name' => null, 'address' => 'eric@zhu.email']], $mailable->cc);
        $this->assertTrue($mailable->hasCc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->cc('eric@zhu.email', 'Eric Zhu');
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->cc);
        $this->assertTrue($mailable->hasCc('eric@zhu.email', 'Eric Zhu'));
        $this->assertTrue($mailable->hasCc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->cc(['eric@zhu.email']);
        $this->assertEquals([['name' => null, 'address' => 'eric@zhu.email']], $mailable->cc);
        $this->assertTrue($mailable->hasCc('eric@zhu.email'));
        $this->assertFalse($mailable->hasCc('eric@zhu.email', 'Eric Zhu'));

        $mailable = new WelcomeMailableStub();
        $mailable->cc([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']]);
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->cc);
        $this->assertTrue($mailable->hasCc('eric@zhu.email', 'Eric Zhu'));
        $this->assertTrue($mailable->hasCc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->cc(new MailableTestUserStub());
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->cc);
        $this->assertTrue($mailable->hasCc(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasCc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->cc(collect([new MailableTestUserStub()]));
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->cc);
        $this->assertTrue($mailable->hasCc(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasCc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->cc(collect([new MailableTestUserStub(), new MailableTestUserStub()]));
        $this->assertEquals([
            ['name' => 'Eric Zhu', 'address' => 'eric@zhu.email'],
            ['name' => 'Eric Zhu', 'address' => 'eric@zhu.email'],
        ], $mailable->cc);
        $this->assertTrue($mailable->hasCc(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasCc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->cc(['eric@zhu.email', 'not-eric@zhu.email']);
        $this->assertEquals([
            ['name' => null, 'address' => 'eric@zhu.email'],
            ['name' => null, 'address' => 'not-eric@zhu.email'],
        ], $mailable->cc);
        $this->assertTrue($mailable->hasCc('eric@zhu.email'));
        $this->assertTrue($mailable->hasCc('not-eric@zhu.email'));
    }

    public function testMailableSetsBccRecipientsCorrectly()
    {
        $mailable = new WelcomeMailableStub();
        $mailable->bcc('eric@zhu.email');
        $this->assertEquals([['name' => null, 'address' => 'eric@zhu.email']], $mailable->bcc);
        $this->assertTrue($mailable->hasBcc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->bcc('eric@zhu.email', 'Eric Zhu');
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->bcc);
        $this->assertTrue($mailable->hasBcc('eric@zhu.email', 'Eric Zhu'));
        $this->assertTrue($mailable->hasBcc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->bcc(['eric@zhu.email']);
        $this->assertEquals([['name' => null, 'address' => 'eric@zhu.email']], $mailable->bcc);
        $this->assertTrue($mailable->hasBcc('eric@zhu.email'));
        $this->assertFalse($mailable->hasBcc('eric@zhu.email', 'Eric Zhu'));

        $mailable = new WelcomeMailableStub();
        $mailable->bcc([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']]);
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->bcc);
        $this->assertTrue($mailable->hasBcc('eric@zhu.email', 'Eric Zhu'));
        $this->assertTrue($mailable->hasBcc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->bcc(new MailableTestUserStub());
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->bcc);
        $this->assertTrue($mailable->hasBcc(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasBcc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->bcc(collect([new MailableTestUserStub()]));
        $this->assertEquals([['name' => 'Eric Zhu', 'address' => 'eric@zhu.email']], $mailable->bcc);
        $this->assertTrue($mailable->hasBcc(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasBcc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->bcc(collect([new MailableTestUserStub(), new MailableTestUserStub()]));
        $this->assertEquals([
            ['name' => 'Eric Zhu', 'address' => 'eric@zhu.email'],
            ['name' => 'Eric Zhu', 'address' => 'eric@zhu.email'],
        ], $mailable->bcc);
        $this->assertTrue($mailable->hasBcc(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasBcc('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->bcc(['eric@zhu.email', 'not-eric@zhu.email']);
        $this->assertEquals([
            ['name' => null, 'address' => 'eric@zhu.email'],
            ['name' => null, 'address' => 'not-eric@zhu.email'],
        ], $mailable->bcc);
        $this->assertTrue($mailable->hasBcc('eric@zhu.email'));
        $this->assertTrue($mailable->hasBcc('not-eric@zhu.email'));
    }

    public function testMailableSetsReplyToCorrectly()
    {
        $mailable = new WelcomeMailableStub();
        $mailable->replyTo('eric@zhu.email');
        $this->assertEquals(['name' => null, 'address' => 'eric@zhu.email'], $mailable->replyTo);
        $this->assertTrue($mailable->hasReplyTo('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->replyTo('eric@zhu.email', 'Eric Zhu');
        $this->assertEquals(['name' => 'Eric Zhu', 'address' => 'eric@zhu.email'], $mailable->replyTo);
        $this->assertTrue($mailable->hasReplyTo('eric@zhu.email', 'Eric Zhu'));
        $this->assertTrue($mailable->hasReplyTo('eric@zhu.email'));

        $mailable = new WelcomeMailableStub();
        $mailable->replyTo(new MailableTestUserStub());
        $this->assertEquals(['name' => 'Eric Zhu', 'address' => 'eric@zhu.email'], $mailable->replyTo);
        $this->assertTrue($mailable->hasReplyTo(new MailableTestUserStub()));
        $this->assertTrue($mailable->hasReplyTo('eric@zhu.email'));
    }

    public function testItIgnoresDuplicatedRawAttachments()
    {
        $mailable = new WelcomeMailableStub();

        $mailable->attachData('content1', 'report-1.txt');
        $this->assertCount(1, $mailable->rawAttachments);

        $mailable->attachData('content2', 'report-2.txt');
        $this->assertCount(2, $mailable->rawAttachments);

        $mailable->attachData('content1', 'report-1.txt');
        $mailable->attachData('content2', 'report-2.txt');
        $this->assertCount(2, $mailable->rawAttachments);

        $mailable->attachData('content1', 'report-3.txt');
        $mailable->attachData('content2', 'report-4.txt');
        $this->assertCount(4, $mailable->rawAttachments);

        $this->assertSame([
            [
                'data' => 'content1',
                'name' => 'report-1.txt',
                'options' => [],
            ],
            [
                'data' => 'content2',
                'name' => 'report-2.txt',
                'options' => [],
            ],
            [
                'data' => 'content1',
                'name' => 'report-3.txt',
                'options' => [],
            ],
            [
                'data' => 'content2',
                'name' => 'report-4.txt',
                'options' => [],
            ],
        ], $mailable->rawAttachments);
    }

    public function testItIgnoresDuplicateStorageAttachments()
    {
        $mailable = new WelcomeMailableStub();

        $mailable->attachFromStorage('disk1', 'sample/file.txt');
        $this->assertCount(1, $mailable->storageAttachments);

        $mailable->attachFromStorage('disk1', 'sample/file2.txt');
        $this->assertCount(2, $mailable->storageAttachments);

        $mailable->attachFromStorage('disk1', 'sample/file.txt', 'file.txt');
        $mailable->attachFromStorage('disk1', 'sample/file2.txt');
        $this->assertCount(2, $mailable->storageAttachments);

        $mailable->attachFromStorage('disk2', 'sample/file.txt', 'file.txt');
        $mailable->attachFromStorage('disk2', 'sample/file2.txt');
        $this->assertCount(4, $mailable->storageAttachments);

        $mailable->attachFromStorage('disk1', 'sample/file.txt', 'custom.txt');
        $this->assertCount(5, $mailable->storageAttachments);

        $this->assertSame([
            [
                'storage' => 'disk1',
                'path' => 'sample/file.txt',
                'name' => 'file.txt',
                'options' => [],
            ],
            [
                'storage' => 'disk1',
                'path' => 'sample/file2.txt',
                'name' => 'file2.txt',
                'options' => [],
            ],
            [
                'storage' => 'disk2',
                'path' => 'sample/file.txt',
                'name' => 'file.txt',
                'options' => [],
            ],
            [
                'storage' => 'disk2',
                'path' => 'sample/file2.txt',
                'name' => 'file2.txt',
                'options' => [],
            ],
            [
                'storage' => 'disk1',
                'path' => 'sample/file.txt',
                'name' => 'custom.txt',
                'options' => [],
            ],
        ], $mailable->storageAttachments);
    }

    public function testMailableBuildsViewData()
    {
        $mailable = new WelcomeMailableStub();

        $mailable->build();

        $expected = [
            'first_name' => 'Eric',
            'last_name' => 'Zhu',
            'framework' => 'Hyperf',
        ];

        $actual = $mailable->buildViewData();

        $this->assertSame($expected, $actual);
    }

    public function testMailerMayBeSet()
    {
        $mailable = new WelcomeMailableStub();

        $mailable->mailer('array');

        $mailable = unserialize(serialize($mailable));

        $this->assertSame('array', $mailable->mailer);
    }
}

class WelcomeMailableStub extends Mailable
{
    public $framework = 'Hyperf';

    protected $version = '2.0';

    /**
     * Build the message.
     */
    public function build()
    {
        $this->with('first_name', 'Eric')
            ->with('last_name', 'Zhu');
    }
}

class MailableTestUserStub implements HasMailAddressInterface
{
    public function getMailAddress(): string
    {
        return 'eric@zhu.email';
    }

    public function getMailAddressDisplayName(): ?string
    {
        return 'Eric Zhu';
    }
}
