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

use Hyperf\Utils\Traits\ForwardsCalls;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

/**
 * @mixin Email
 */
class Message
{
    use ForwardsCalls;

    /**
     * CIDs of files embedded in the message.
     */
    protected array $embeddedFiles = [];

    protected array $data = [];

    /**
     * Create a new message instance.
     */
    public function __construct(protected Email $email)
    {
    }

    /**
     * Dynamically pass missing methods to the Swift instance.
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->email, $method, $parameters);
    }

    /**
     * Add a "from" address to the message.
     */
    public function setFrom(array|string|null $address, ?string $name = null): self
    {
        $this->setAddresses($address, $name, 'From');

        return $this;
    }

    /**
     * Add a reply to address to the message.
     */
    public function setReplyTo(array|string|null $address, ?string $name = null): self
    {
        return $this->setAddresses($address, $name, 'ReplyTo');
    }

    /**
     * Set the "sender" of the message.
     * @param mixed $address
     */
    public function setSender($address, ?string $name = null): self
    {
        $this->email->sender($this->prepareAddress($address, $name)[0]);

        return $this;
    }

    /**
     * Set the "return path" of the message.
     */
    public function setReturnPath(string $address): self
    {
        $this->email->returnPath($address);

        return $this;
    }

    /**
     * Set the recipient addresses of this message.
     */
    public function setTo(array|string|null $address, ?string $name = null): self
    {
        return $this->setAddresses($address, $name, 'To');
    }

    /**
     * Add a carbon copy to the message.
     */
    public function setCc(array|string|null $address, ?string $name = null, bool $override = false): self
    {
        if ($override) {
            $this->setAddresses($address, $name, 'Cc');
        } else {
            $this->addAddresses($address, $name, 'Cc');
        }

        return $this;
    }

    /**
     * Add a blind carbon copy to the message.
     */
    public function setBcc(array|string|null $address, ?string $name = null, bool $override = false): self
    {
        if ($override) {
            $this->setAddresses($address, $name, 'Bcc');
        } else {
            $this->addAddresses($address, $name, 'Bcc');
        }
        return $this;
    }

    /**
     * Set the subject of the message.
     */
    public function setSubject(string $subject): self
    {
        $this->email->subject($subject);

        return $this;
    }

    /**
     * Set the message priority level.
     */
    public function setPriority(int $level): self
    {
        $this->email->priority($level);

        return $this;
    }

    /**
     * Attach a file to the message.
     */
    public function attachFile(string $file, array $options = []): self
    {
        $attachment = $this->createAttachmentFromPath($file, $options);
        $this->email->attachPart($attachment);

        return $this;
    }

    /**
     * Attach in-memory data as an attachment.
     */
    public function attachData(string $data, string $name, array $options = []): self
    {
        $attachment = $this->createAttachmentFromData($data, $name, $options['mime'] ?? null);
        $this->email->attachPart($attachment);

        return $this;
    }

    /**
     * Embed a file in the message and get the CID.
     */
    public function embed(string $file): string
    {
        if (isset($this->embeddedFiles[$file])) {
            return $this->embeddedFiles[$file];
        }

        $dataPart = DataPart::fromPath($file);
        $this->email->attachPart($dataPart->asInline());

        return $this->embeddedFiles[$file] = $dataPart->getContentId();
    }

    /**
     * Embed in-memory data in the message and get the CID.
     */
    public function embedData(string $data, string $name, ?string $contentType = null): string
    {
        $dataPart = new DataPart($data, $name, $contentType);
        $this->email->attachPart($dataPart->asInline());

        return $dataPart->getContentId();
    }

    /**
     * Get the underlying Email instance.
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Add a recipient to the message.
     */
    protected function addAddresses(array|string|null $address, ?string $name = null, string $type = 'To'): self
    {
        $this->email->{"add{$type}"}(...$this->prepareAddress($address, $name));

        return $this;
    }

    /**
     * Set a recipient to the message.
     */
    protected function setAddresses(array|string|null $address, ?string $name = null, string $type = 'To'): self
    {
        $this->email->{lcfirst($type)}(...$this->prepareAddress($address, $name));

        return $this;
    }

    /**
     * Create a DataPart instance.
     */
    protected function createAttachmentFromPath(string $file, array $options): DataPart
    {
        return DataPart::fromPath($file, $options['as'] ?? null, $options['mime'] ?? null);
    }

    /**
     * Create a DataPart instance from data.
     */
    protected function createAttachmentFromData(string $data, string $name, ?string $type = null): DataPart
    {
        return new DataPart($data, $name, $type);
    }

    /**
     * @return Address[]
     */
    private function prepareAddress(array|string|null $address, ?string $name): array
    {
        $result = [];

        if (is_null($address)) {
            return $result;
        }

        if (! is_array($address)) {
            $result[] = new Address($address, $name ?? '');
        } else {
            foreach ($address as $key => $item) {
                if (is_array($item)) {
                    $result[] = new Address($item['address'], $item['name']);
                } elseif (is_string($key)) {
                    $result[] = new Address($key, $item);
                } else {
                    $result[] = new Address($item);
                }
            }
        }
        return $result;
    }
}
