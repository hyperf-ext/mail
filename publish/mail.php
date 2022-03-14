<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/mail.
 *
 * @link     https://github.com/hyperf-ext/mail
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/mail/blob/master/LICENSE
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Supports a variety of mail "transport" drivers to be used while
    | sending an e-mail. You will specify which one you are using for your
    | mailers below. You are free to add additional mailers as required.
    |
    */

    'mailers' => [
        'smtp' => [
            // smtp://user:pass@smtp.example.com:port
            'dsn' => env('MAIL_SMTP_DSN'),
        ],

        'aws_ses' => [
            // ses+smtp://USERNAME:PASSWORD@default
            // ses+https://ACCESS_KEY:SECRET_KEY@default
            // ses+api://ACCESS_KEY:SECRET_KEY@default
            'dsn' => env('MAIL_AWS_SES_DSN'),
        ],

        'mandrill' => [
            // mandrill+smtp://USERNAME:PASSWORD@default
            // mandrill+https://KEY@default
            // mandrill+api://KEY@default
            'dsn' => env('MAIL_MANDRILL_DSN'),
        ],

        'mailgun' => [
            // mailgun+smtp://USERNAME:PASSWORD@default
            // mailgun+https://KEY:DOMAIN@default
            // mailgun+api://KEY:DOMAIN@default
            'dsn' => env('MAIL_MAILGUN_DSN'),
        ],

        'postmark' => [
            // postmark+smtp://ID@default
            // postmark+api://KEY@default
            'dsn' => env('MAIL_POSTMARK_DSN'),
        ],

        'sendmail' => [
            'dsn' => 'sendmail://default',
        ],

        'log' => [
            'dsn' => 'log://',
            'transport' => \HyperfExt\Mail\Transport\LogTransport::class,
            'options' => [
                'name' => 'mail.local',
                'group' => 'default',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logger Options
    |--------------------------------------------------------------------------
    |
    | The `hyperf/logger` component is required if enabled.
    */

    'logger' => [
        'enabled' => false,
        'name' => 'mail',
        'group' => 'default',
    ],
];
