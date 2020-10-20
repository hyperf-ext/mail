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

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use HyperfExt\Mail\Contracts\MailManagerInterface;
use HyperfExt\Mail\MailManager;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class MailManagerTest extends TestCase
{
    public function testEmptyTransportConfig()
    {
        $transport = '';

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(m::mock());
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'mail' => [
                'mailers' => [
                    'custom_smtp' => [
                        'transport' => $transport,
                        'options' => [
                            'host' => null,
                            'port' => null,
                            'encryption' => null,
                            'username' => null,
                            'password' => null,
                            'timeout' => null,
                        ],
                    ],
                ],
            ],
        ]));
        $container->shouldReceive('get')->with(MailManagerInterface::class)->andReturn(new MailManager($container));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The mail transport must be specified.');
        $container->get(MailManagerInterface::class)->mailer('custom_smtp');
    }
}
