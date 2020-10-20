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

use AlibabaCloud\Client\AlibabaCloud;
use Swift_Mime_SimpleMessage;

class AliyunDmTransport extends Transport
{
    /**
     * The Alibaba Cloud AccessKeyClient instance.
     *
     * @var \AlibabaCloud\Client\Clients\AccessKeyClient
     */
    protected $client;

    /**
     * The Alibaba Cloud DM transmission options.
     *
     * @var array
     */
    protected $options = [];

    public function __construct(array $options = [])
    {
        $this->client = AlibabaCloud::accessKeyClient($options['access_key_id'], $options['access_secret'])
            ->regionId($options['region_id'])
            ->asDefaultClient();
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $query = [
            'RegionId' => $this->options['region_id'],
            'AddressType' => '1',
            'ClickTrace' => $this->options['click_trace'] ?? '0',
            'ReplyToAddress' => false,
            'AccountName' => $fromAddress = array_key_first($from = $message->getFrom()),
            'FromAlias' => $from[$fromAddress],
            'Subject' => $message->getSubject(),
            'ToAddress' => implode(',', array_keys($message->getTo())),
        ];

        foreach (array_merge([$message], $message->getChildren()) as $entity) {
            $contentType = $entity->getBodyContentType();
            if ($contentType === 'text/html') {
                $query['HtmlBody'] = $entity->getBody();
            } elseif ($contentType === 'text/plain') {
                $query['TextBody'] = $entity->getBody();
            }
        }

        $result = AlibabaCloud::rpc()
            ->product('Dm')
            ->scheme('https')
            ->version('2015-11-23')
            ->action('SingleSendMail')
            ->method('POST')
            ->host('dm.aliyuncs.com')
            ->options(compact('query'))
            ->request();

        $headers = $message->getHeaders();
        $headers->addTextHeader('X-Aliyun-DM-Env-ID', $result->get('EnvId'));
        $headers->addTextHeader('X-Aliyun-DM-Request-ID', $result->get('RequestId'));

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }
}
