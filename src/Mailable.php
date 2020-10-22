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

use Closure;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Contract\UnCompressInterface;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Str;
use Hyperf\Utils\Traits\ForwardsCalls;
use Hyperf\View\RenderInterface;
use HyperfExt\Contract\HasMailAddress;
use HyperfExt\Mail\Contracts\MailableInterface;
use HyperfExt\Mail\Contracts\MailerInterface;
use HyperfExt\Mail\Contracts\MailManagerInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class Mailable implements MailableInterface, CompressInterface, UnCompressInterface
{
    use ForwardsCalls;

    /**
     * The locale of the message.
     *
     * @var string
     */
    public $locale;

    /**
     * The person the message is from.
     *
     * @var array
     */
    public $from;

    /**
     * The "to" recipients of the message.
     *
     * @var array
     */
    public $to = [];

    /**
     * The "cc" recipients of the message.
     *
     * @var array
     */
    public $cc = [];

    /**
     * The "bcc" recipients of the message.
     *
     * @var array
     */
    public $bcc = [];

    /**
     * The "reply to" recipients of the message.
     *
     * @var array
     */
    public $replyTo;

    /**
     * The subject of the message.
     *
     * @var string
     */
    public $subject;

    /**
     * The HTML view to use for the message.
     *
     * @var string
     */
    public $htmlViewTemplate;

    /**
     * The plain text view to use for the message.
     *
     * @var string
     */
    public $textViewTemplate;

    /**
     * The view data for the message.
     *
     * @var array
     */
    public $viewData = [];

    /**
     * The HTML content to use for the message.
     *
     * @var string
     */
    public $htmlBody;

    /**
     * The plain text content to use for the message.
     *
     * @var string
     */
    public $textBody;

    /**
     * The attachments for the message.
     *
     * @var array
     */
    public $attachments = [];

    /**
     * The raw attachments for the message.
     *
     * @var array
     */
    public $rawAttachments = [];

    /**
     * The attachments from a storage disk.
     *
     * @var array
     */
    public $storageAttachments = [];

    /**
     * The priority of this message.
     *
     * @var int
     */
    public $priority = 3;

    /**
     * The name of the mailer that should send the message.
     *
     * @var string
     */
    public $mailer;

    /**
     * The callbacks for the message.
     *
     * @var \Closure[]
     */
    public $callbacks = [];

    /**
     * The callback that should be invoked while building the view data.
     *
     * @var callable
     */
    public static $viewDataCallback;

    public function locale(string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function priority(int $level)
    {
        $this->priority = $level;

        return $this;
    }

    public function from($address, ?string $name = null)
    {
        $this->from = $this->normalizeRecipient($address, $name);

        return $this;
    }

    public function hasFrom($address, ?string $name = null): bool
    {
        return $this->from == $this->normalizeRecipient($address, $name);
    }

    public function replyTo($address, ?string $name = null)
    {
        $this->replyTo = $this->normalizeRecipient($address, $name);

        return $this;
    }

    public function hasReplyTo($address, ?string $name = null): bool
    {
//        return $this->replyTo == $this->normalizeRecipient($address, $name);
        return $this->hasRecipient($address, $name, 'replyTo');
    }

    public function to($address, ?string $name = null)
    {
        return $this->addRecipient($address, $name, 'to');
    }

    public function hasTo($address, ?string $name = null): bool
    {
        return $this->hasRecipient($address, $name, 'to');
    }

    public function cc($address, ?string $name = null)
    {
        return $this->addRecipient($address, $name, 'cc');
    }

    public function hasCc($address, ?string $name = null): bool
    {
        return $this->hasRecipient($address, $name, 'cc');
    }

    public function bcc($address, ?string $name = null)
    {
        return $this->addRecipient($address, $name, 'bcc');
    }

    public function hasBcc($address, ?string $name = null): bool
    {
        return $this->hasRecipient($address, $name, 'bcc');
    }

    public function subject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function attach(string $file, array $options = [])
    {
        $this->attachments = collect($this->attachments)
            ->push(compact('file', 'options'))
            ->unique('file')
            ->all();

        return $this;
    }

    public function attachFromStorage(?string $adapter, string $path, ?string $name = null, array $options = [])
    {
        $this->storageAttachments = collect($this->storageAttachments)->push([
            'storage' => $adapter ?: config('file.default'),
            'path' => $path,
            'name' => $name ?? basename($path),
            'options' => $options,
        ])->unique(function ($file) {
            return $file['name'] . $file['storage'] . $file['path'];
        })->all();

        return $this;
    }

    /**
     * Attach a file to the message from storage.
     *
     * @return $this
     */
    public function attachFromDefaultStorage(string $path, ?string $name = null, array $options = [])
    {
        return $this->attachFromStorage(null, $path, $name, $options);
    }

    public function attachData(string $data, string $name, array $options = [])
    {
        $this->rawAttachments = collect($this->rawAttachments)
            ->push(compact('data', 'name', 'options'))
            ->unique(function ($file) {
                return $file['name'] . $file['data'];
            })->all();

        return $this;
    }

    public function mailer(string $mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }

    /**
     * Register a callback to be called with the Swift message instance.
     *
     * @return $this
     */
    public function withSwiftMessage(Closure $callback)
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to be called while building the view data.
     */
    public static function buildViewDataUsing(callable $callback): void
    {
        static::$viewDataCallback = $callback;
    }

    public function htmlView(string $template)
    {
        $this->htmlViewTemplate = $template;

        return $this;
    }

    public function textView(string $template)
    {
        $this->textViewTemplate = $template;

        return $this;
    }

    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } elseif (is_string($key)) {
            $this->viewData[$key] = $value;
        }

        return $this;
    }

    public function htmlBody(string $content)
    {
        $this->htmlBody = $content;

        return $this;
    }

    public function textBody(string $content)
    {
        $this->textBody = $content;

        return $this;
    }

    public function handler(Message $message)
    {
        $mailable = clone $this;

        call([$mailable, 'build']);

        $data = $mailable->buildViewData();
        $data['message'] = $message;
        [$html, $plain] = $mailable->buildView($data);

        $mailable
            ->buildAddresses($message)
            ->buildSubject($message)
            ->runCallbacks($message)
            ->buildAttachments($message)
            ->buildContents($message, $html, $plain, $data);
    }

    public function render($mailer = null): string
    {
        $mailer = $this->resolveMailer($mailer);

        return $mailer->render($this);
    }

    public function send($mailer = null): array
    {
        $mailer = $this->resolveMailer($mailer);

        return $mailer->sendNow($this);
    }

    public function queue(?string $queue = null): bool
    {
        $queue = $queue ?: (property_exists($this, 'queue') ? $this->queue : array_key_first(config('async_queue')));

        return ApplicationContext::getContainer()->get(DriverFactory::class)->get($queue)->push($this->newQueuedJob());
    }

    public function later(int $delay, ?string $queue = null): bool
    {
        $queue = $queue ?: (property_exists($this, 'queue') ? $this->queue : array_key_first(config('async_queue')));

        return ApplicationContext::getContainer()->get(DriverFactory::class)->get($queue)->push($this->newQueuedJob(), $delay);
    }

    /**
     * @return static
     */
    public function uncompress(): CompressInterface
    {
        foreach ($this as $key => $value) {
            if ($value instanceof UnCompressInterface) {
                $this->{$key} = $value->uncompress();
            }
        }

        return $this;
    }

    /**
     * @return static
     */
    public function compress(): UnCompressInterface
    {
        foreach ($this as $key => $value) {
            if ($value instanceof CompressInterface) {
                $this->{$key} = $value->compress();
            }
        }

        return $this;
    }

    public function buildViewData(): array
    {
        $data = $this->viewData;

        if (static::$viewDataCallback) {
            $data = array_merge($data, call_user_func(static::$viewDataCallback, $this));
        }

        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->getDeclaringClass()->getName() !== self::class) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }

        return $data;
    }

    /**
     * @param null|\HyperfExt\Mail\Contracts\MailerInterface|\HyperfExt\Mail\Contracts\MailManagerInterface $mailer
     */
    protected function resolveMailer($mailer = null): MailerInterface
    {
        return empty($mailer)
            ? ApplicationContext::getContainer()->get(MailManagerInterface::class)->mailer($this->mailer)
            : ($mailer instanceof MailManager ? $mailer->mailer($this->mailer) : $mailer);
    }

    /**
     * Make the queued mailable job instance.
     */
    protected function newQueuedJob(): QueuedMailableJob
    {
        return new QueuedMailableJob($this);
    }

    /**
     * @param array|Collection|string $address
     *
     * @return $this
     */
    protected function addRecipient($address, ?string $name = null, string $property = 'to')
    {
        $this->{$property} = array_merge($this->{$property}, $this->arrayizeAddress($address, $name));

        return $this;
    }

    /**
     * Convert the given recipient arguments to an array.
     *
     * @param array|Collection|string $address
     */
    protected function arrayizeAddress($address, ?string $name = null): array
    {
        $addresses = [];
        if (is_array($address) or $address instanceof Collection) {
            foreach ($address as $item) {
                if (is_array($item) && isset($item['address'])) {
                    $addresses[] = [
                        'address' => $item['address'],
                        'name' => $item['name'] ?? null,
                    ];
                } elseif (is_string($item) or $item instanceof HasMailAddress) {
                    $addresses[] = $this->normalizeRecipient($item);
                }
            }
        } else {
            $addresses[] = $this->normalizeRecipient($address, $name);
        }
        return $addresses;
    }

    /**
     * Convert the given recipient into an object.
     *
     * @param HasMailAddress|string $address
     */
    protected function normalizeRecipient($address, ?string $name = null): array
    {
        if ($address instanceof HasMailAddress) {
            $name = $address->getMailAddressDisplayName();
            $address = $address->getMailAddress();
        }

        return compact('address', 'name');
    }

    /**
     * Determine if the given recipient is set on the mailable.
     *
     * @param array|object|string $address
     */
    protected function hasRecipient($address, ?string $name = null, string $property = 'to'): bool
    {
        $expected = $this->arrayizeAddress($address, $name)[0];

        $expected = [
            'name' => $expected['name'] ?? null,
            'address' => $expected['address'],
        ];

        return collect(in_array($property, ['replyTo', 'from']) ? [$this->{$property}] : $this->{$property})->contains(function ($actual) use ($expected) {
            if (! isset($expected['name'])) {
                return $actual['address'] == $expected['address'];
            }

            return $actual == $expected;
        });
    }

    protected function buildView(array $data): array
    {
        Coroutine::create(function () use ($data, &$html, &$plain) {
            if (! empty($this->locale)) {
                ApplicationContext::getContainer()->get(TranslatorInterface::class)->setLocale($this->locale);
            }

            $html = $plain = null;

            if (! empty($this->htmlBody)) {
                $html = $this->htmlBody;
            } elseif (! empty($this->htmlViewTemplate)) {
                $html = $this->renderView($this->htmlViewTemplate, $data);
            }

            if (! empty($this->textBody)) {
                $plain = $this->textBody;
            } elseif (! empty($this->textViewTemplate)) {
                $plain = $this->renderView($this->textViewTemplate, $data);
            }
        });

        return [$html, $plain];
    }

    /**
     * Render the given view.
     */
    protected function renderView(string $view, array $data): string
    {
        return ApplicationContext::getContainer()->get(RenderInterface::class)->getContents($view, $data);
    }

    /**
     * Add all of the addresses to the message.
     *
     * @return $this
     */
    protected function buildAddresses(Message $message)
    {
        foreach (['from', 'replyTo'] as $type) {
            is_array($this->{$type}) && $message->{'set' . ucfirst($type)}($this->{$type}['address'], $this->{$type}['name']);
        }

        foreach (['to', 'cc', 'bcc'] as $type) {
            foreach ($this->{$type} as $recipient) {
                $message->{'set' . ucfirst($type)}($recipient['address'], $recipient['name']);
            }
        }

        return $this;
    }

    /**
     * Set the subject for the message.
     *
     * @return $this
     */
    protected function buildSubject(Message $message)
    {
        if ($this->subject) {
            $message->setSubject($this->subject);
        } else {
            $message->setSubject(Str::title(Str::snake(class_basename($this), ' ')));
        }

        return $this;
    }

    /**
     * Add all of the attachments to the message.
     *
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return $this
     */
    protected function buildAttachments(Message $message)
    {
        foreach ($this->attachments as $attachment) {
            $message->attach($attachment['file'], $attachment['options']);
        }

        foreach ($this->rawAttachments as $attachment) {
            $message->attachData(
                $attachment['data'],
                $attachment['name'],
                $attachment['options']
            );
        }

        // Add all of the adapter attachments to the message.
        foreach ($this->storageAttachments as $attachment) {
            $storage = ApplicationContext::getContainer()->get(FilesystemFactory::class)->get($attachment['storage']);

            $message->attachData(
                $storage->read($attachment['path']),
                $attachment['name'] ?? basename($attachment['path']),
                array_merge(['mime' => $storage->getMimetype($attachment['path'])], $attachment['options'])
            );
        }

        return $this;
    }

    /**
     * Add the content to a given message.
     *
     * @return $this
     */
    protected function buildContents(Message $message, ?string $html, ?string $plain, array $data)
    {
        if (! empty($html)) {
            $message->setBody($html, 'text/html');
        }

        if (! empty($plain)) {
            $method = empty($html) ? 'setBody' : 'addPart';

            $message->{$method}($plain ?: ' ', 'text/plain');
        }

        $message->setData($data);

        return $this;
    }

    /**
     * Run the callbacks for the message.
     *
     * @return $this
     */
    protected function runCallbacks(Message $message)
    {
        foreach ($this->callbacks as $callback) {
            $callback($message->getSwiftMessage());
        }

        return $this;
    }
}
