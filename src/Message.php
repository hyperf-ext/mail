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
use Swift_Attachment;
use Swift_Image;
use Swift_Message;

/**
 * @mixin \Swift_Message
 */
class Message
{
    use ForwardsCalls;

    /**
     * The Swift Message instance.
     *
     * @var \Swift_Message
     */
    protected $swift;

    /**
     * CIDs of files embedded in the message.
     *
     * @var array
     */
    protected $embeddedFiles = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Create a new message instance.
     */
    public function __construct(Swift_Message $swift)
    {
        $this->swift = $swift;
    }

    /**
     * Dynamically pass missing methods to the Swift instance.
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->swift, $method, $parameters);
    }

    /**
     * Add a "from" address to the message.
     *
     * @param array|string $address
     * @return $this
     */
    public function setFrom($address, ?string $name = null)
    {
        $this->swift->setFrom($address, $name);

        return $this;
    }

    /**
     * Add a reply to address to the message.
     *
     * @param array|string $address
     * @return $this
     */
    public function setReplyTo($address, ?string $name = null)
    {
        return $this->addAddresses($address, $name, 'ReplyTo');
    }

    /**
     * Set the "sender" of the message.
     *
     * @param array|string $address
     * @return $this
     */
    public function setSender($address, ?string $name = null)
    {
        $this->swift->setSender($address, $name);

        return $this;
    }

    /**
     * Set the "return path" of the message.
     *
     * @return $this
     */
    public function setReturnPath(string $address)
    {
        $this->swift->setReturnPath($address);

        return $this;
    }

    /**
     * Set the recipient addresses of this message.
     *
     * @param array|string $address
     * @return $this
     */
    public function setTo($address, ?string $name = null)
    {
        return $this->addAddresses($address, $name, 'To');
    }

    /**
     * Add a carbon copy to the message.
     *
     * @param array|string $address
     * @return $this
     */
    public function setCc($address, ?string $name = null, bool $override = false)
    {
        if ($override) {
            $this->swift->setCc($address, $name);

            return $this;
        }

        return $this->addAddresses($address, $name, 'Cc');
    }

    /**
     * Add a blind carbon copy to the message.
     *
     * @param array|string $address
     * @return $this
     */
    public function setBcc($address, ?string $name = null, bool $override = false)
    {
        if ($override) {
            $this->swift->setBcc($address, $name);

            return $this;
        }

        return $this->addAddresses($address, $name, 'Bcc');
    }

    /**
     * Set the subject of the message.
     *
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->swift->setSubject($subject);

        return $this;
    }

    /**
     * Set the message priority level.
     *
     * @return $this
     */
    public function setPriority(int $level)
    {
        $this->swift->setPriority($level);

        return $this;
    }

    /**
     * Attach a file to the message.
     *
     * @return $this
     */
    public function attach(string $file, array $options = [])
    {
        $attachment = $this->createAttachmentFromPath($file);

        return $this->prepAttachment($attachment, $options);
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @return $this
     */
    public function attachData(string $data, string $name, array $options = [])
    {
        $attachment = $this->createAttachmentFromData($data, $name);

        return $this->prepAttachment($attachment, $options);
    }

    /**
     * Embed a file in the message and get the CID.
     */
    public function embed(string $file): string
    {
        if (isset($this->embeddedFiles[$file])) {
            return $this->embeddedFiles[$file];
        }

        return $this->embeddedFiles[$file] = $this->swift->embed(
            Swift_Image::fromPath($file)
        );
    }

    /**
     * Embed in-memory data in the message and get the CID.
     */
    public function embedData(string $data, string $name, ?string $contentType = null): string
    {
        $image = new Swift_Image($data, $name, $contentType);

        return $this->swift->embed($image);
    }

    /**
     * Get the underlying Swift Message instance.
     *
     * @return \Swift_Message
     */
    public function getSwiftMessage()
    {
        return $this->swift;
    }

    public function setData(array $data)
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
     *
     * @param array|string $address
     * @return $this
     */
    protected function addAddresses($address, ?string $name, string $type)
    {
        if (is_array($address)) {
            foreach ($address as $item) {
                $this->swift->{"add{$type}"}($item['address'], $item['name']);
            }
        } else {
            $this->swift->{"add{$type}"}($address, $name);
        }

        return $this;
    }

    /**
     * Create a Swift Attachment instance.
     *
     * @return \Swift_Attachment
     */
    protected function createAttachmentFromPath(string $file)
    {
        return Swift_Attachment::fromPath($file);
    }

    /**
     * Create a Swift Attachment instance from data.
     *
     * @return \Swift_Attachment
     */
    protected function createAttachmentFromData(string $data, string $name)
    {
        return new Swift_Attachment($data, $name);
    }

    /**
     * Prepare and attach the given attachment.
     *
     * @return $this
     */
    protected function prepAttachment(Swift_Attachment $attachment, array $options = [])
    {
        // First we will check for a MIME type on the message, which instructs the
        // mail client on what type of attachment the file is so that it may be
        // downloaded correctly by the user. The MIME option is not required.
        if (isset($options['mime'])) {
            $attachment->setContentType($options['mime']);
        }

        // If an alternative name was given as an option, we will set that on this
        // attachment so that it will be downloaded with the desired names from
        // the developer, otherwise the default file names will get assigned.
        if (isset($options['as'])) {
            $attachment->setFilename($options['as']);
        }

        $this->swift->attach($attachment);

        return $this;
    }
}
