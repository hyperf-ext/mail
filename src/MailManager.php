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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use HyperfExt\Contract\ShouldQueue;
use HyperfExt\Mail\Concerns\PendingMailable;
use HyperfExt\Mail\Contracts\MailableInterface;
use HyperfExt\Mail\Contracts\MailerInterface;
use HyperfExt\Mail\Contracts\MailManagerInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Swift_DependencyContainer;
use Swift_Mailer;

/**
 * @mixin \HyperfExt\Mail\Mailer
 */
class MailManager implements MailManagerInterface
{
    use PendingMailable;

    /**
     * The container instance.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * The config instance.
     *
     * @var \Hyperf\Contract\ConfigInterface
     */
    protected $config;

    /**
     * The array of resolved mailers.
     *
     * @var \HyperfExt\Mail\Mailer[]
     */
    protected $mailers = [];

    /**
     * Create a new Mail manager instance.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->mailer()->{$method}(...$arguments);
    }

    /**
     * Get a mailer instance by name.
     */
    public function mailer(?string $name = null): MailerInterface
    {
        $name = $name ?: $this->getDefaultMailerName();

        return $this->get($name);
    }

    /**
     * Get a mailer instance by name.
     */
    public function get(string $name): MailerInterface
    {
        if (empty($this->mailers[$name])) {
            $this->mailers[$name] = $this->resolve($name);
        }

        return $this->mailers[$name];
    }

    /**
     * Send the given mailable.
     *
     * @return mixed
     */
    public function send(MailableInterface $mailable)
    {
        return $mailable instanceof ShouldQueue
            ? $mailable->queue()
            : $mailable->send($this);
    }

    /**
     * Create a new transport instance.
     *
     * @return \Swift_Transport
     */
    protected function createTransport(array $config)
    {
        if (empty($config['transport'])) {
            throw new InvalidArgumentException('The mail transport must be specified.');
        }

        if (! class_exists($config['transport'])) {
            throw new InvalidArgumentException("Unsupported mail transport [{$config['transport']}].");
        }

        return make($config['transport'], ['options' => $config['options'] ?? []]);
    }

    /**
     * Get the default mail driver name.
     *
     * @return string
     */
    protected function getDefaultMailerName()
    {
        return $this->config->get('mail.default');
    }

    /**
     * Resolve the given mailer.
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve(string $name): Mailer
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Mailer [{$name}] is not defined.");
        }

        // Once we have created the mailer instance we will set a container instance
        // on the mailer. This allows us to resolve mailer classes via containers
        // for maximum testability on said classes instead of passing Closures.
        $swift = $this->createSwiftMailer($config);
        $mailer = make(Mailer::class, compact('name', 'swift'));

        // Next we will set all of the global addresses on this mailer, which allows
        // for easy unification of all "from" addresses as well as easy debugging
        // of sent messages since these will be sent to a single email address.
        foreach (['from', 'reply_to', 'to', 'return_path'] as $type) {
            $this->setGlobalAddress($mailer, $config, $type);
        }

        return $mailer;
    }

    /**
     * Create the SwiftMailer instance for the given configuration.
     *
     * @return \Swift_Mailer
     */
    protected function createSwiftMailer(array $config)
    {
        if ($config['domain'] ?? false) {
            Swift_DependencyContainer::getInstance()
                ->register('mime.idgenerator.idright')
                ->asValue($config['domain']);
        }

        $swift = new Swift_Mailer($this->createTransport($config));

        if (($loggerConfig = $this->config->get('mail.logger')) && $loggerConfig['enabled'] === true) {
            $swift->registerPlugin(new SwiftMailerLoggerPlugin(
                $this->container->get(LoggerFactory::class)->get(
                    $loggerConfig['name'] ?? 'mail',
                    $loggerConfig['group'] ?? 'default'
                )
            ));
        }

        return $swift;
    }

    /**
     * Set a global address on the mailer by type.
     */
    protected function setGlobalAddress(MailerInterface $mailer, array $config, string $type)
    {
        $address = Arr::get($config, $type, $this->config->get('mail.' . $type));

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'setAlways' . Str::studly($type)}($address['address'], $address['name']);
        }
    }

    /**
     * Get the mail connection configuration.
     *
     * @return array
     */
    protected function getConfig(string $name)
    {
        return $this->config->get("mail.mailers.{$name}");
    }
}
