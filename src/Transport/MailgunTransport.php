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

use Hyperf\Guzzle\ClientFactory;
use Psr\Http\Message\ResponseInterface;
use Swift_Mime_SimpleMessage;

class MailgunTransport extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The Mailgun API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The Mailgun email domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * The Mailgun API endpoint.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Create a new Mailgun transport instance.
     */
    public function __construct(ClientFactory $clientFactory, array $options)
    {
        $this->client = $clientFactory->create($options['guzzle'] ?? []);
        $this->key = $options['key'];
        $this->endpoint = $options['endpoint'] ?? 'api.mailgun.net';
        $this->domain = $options['domain'];
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $to = $this->getTo($message);

        $bcc = $message->getBcc();

        $message->setBcc([]);

        $response = $this->client->request(
            'POST',
            "https://{$this->endpoint}/v3/{$this->domain}/messages.mime",
            $this->payload($message, $to)
        );

        $message->getHeaders()->addTextHeader(
            'X-Mailgun-Message-ID',
            $this->getMessageId($response)
        );

        $message->setBcc($bcc);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get the HTTP payload for sending the Mailgun message.
     */
    protected function payload(Swift_Mime_SimpleMessage $message, string $to): array
    {
        return [
            'auth' => [
                'api',
                $this->key,
            ],
            'multipart' => [
                [
                    'name' => 'to',
                    'contents' => $to,
                ],
                [
                    'name' => 'message',
                    'contents' => $message->toString(),
                    'filename' => 'message.mime',
                ],
            ],
        ];
    }

    /**
     * Get the "to" payload field for the API request.
     */
    protected function getTo(Swift_Mime_SimpleMessage $message): string
    {
        return collect($this->allContacts($message))->map(function ($display, $address) {
            return $display ? $display . " <{$address}>" : $address;
        })->values()->implode(',');
    }

    /**
     * Get all of the contacts for the message.
     */
    protected function allContacts(Swift_Mime_SimpleMessage $message): array
    {
        return array_merge(
            (array) $message->getTo(),
            (array) $message->getCc(),
            (array) $message->getBcc()
        );
    }

    /**
     * Get the message ID from the response.
     */
    protected function getMessageId(ResponseInterface $response): string
    {
        return data_get(
            json_decode($response->getBody()->getContents()),
            'id'
        );
    }
}
