<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/mail.
 *
 * @link     https://github.com/hyperf-ext/mail
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/mail/blob/master/LICENSE
 */
namespace HyperfExt\Mail\Transport;

use Aws\Ses\SesClient;
use Swift_Mime_SimpleMessage;

class AwsSesTransport extends Transport
{
    /**
     * The Amazon SES instance.
     *
     * @var \Aws\Ses\SesClient
     */
    protected $ses;

    /**
     * The Amazon SES transmission options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new SES transport instance.
     */
    public function __construct(SesClient $ses, array $options = [])
    {
        $this->ses = $ses;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $result = $this->ses->sendRawEmail(
            array_merge(
                $this->options,
                [
                    'Source' => key($message->getSender() ?: $message->getFrom()),
                    'RawMessage' => [
                        'Data' => $message->toString(),
                    ],
                ]
            )
        );

        $message->getHeaders()->addTextHeader('X-AWS-SES-Message-ID', $result->get('MessageId'));

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }
}
