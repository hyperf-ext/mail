# Hyperf 邮件组件

- [简介](#introduction)
    - [安装](#installation)
    - [驱动前提](#driver-prerequisites)
- [创建通知](#generating-mailables)
- [编写通知](#writing-mailables)
    - [配置发送者](#configuring-the-sender)
    - [配置内容](#configuring-the-body)
    - [附件](#attachments)
    - [内部附件](#inline-attachments)
    - [自定义 SwiftMail 消息](#customizing-the-swiftmailer-message)
- [发送邮件](#sending-mail)
    - [队列邮件](#queueing-mail)
- [渲染通知](#rendering-mailables)
    - [在浏览器中预览邮件通知](#previewing-mailables-in-the-browser)
- [本地化通知](#localizing-mailables)
- [邮件与本地开发](#mail-and-local-development)
- [事件](#events)

<a name="introduction"></a>
## 简介

该组件衍生自 [illuminate/mail](https://github.com/illuminate/mail )，基于 [SwiftMailer](https://swiftmailer.symfony.com/) 函数库提供了一套干净、简洁的 API ，可以为 SMTP、Mailgun、Postmark、AWS SES、阿里云 DM 和 `sendmail` 提供驱动，让你可以快速从本地或云端服务自由地发送邮件。

<a name="installation"></a>
### 安装

```shell script
composer require hyperf-ext/mail
```

#### 发布配置

```shell script
php bin/hyperf.php vendor:publish hyperf-ext/mail
```

发布的配置文件中配置的每个邮件程序都可能有自己的「传输方式」和配置选项，这将允许你的应用程序使用不同的邮件服务来发送特定的邮件。例如，你的应用程序可能使用 Postmark 发送事务性邮件，而使用 AWS SES 发送批量邮件。

<a name="driver-prerequisites"></a>
### 驱动前提

基于 API 的驱动，比如 Mailgun 和 Postmark 通常比 SMTP 服务器更简单快速。如果可以的话，你应该尽可能使用这些驱动。所有的 API 驱动都需要 [Hyperf Guzzle](https://hyperf.wiki/2.0/#/zh-cn/guzzle) 组件，这个函数库可以通过 Composer 包管理安装：

```shell script
composer require hyperf/guzzle
```

#### Mailgun 驱动

要使用 Mailgun 驱动，首先必须安装 Hyperf Guzzle 组件, 之后将 `config/autoload/mail.php` 配置文件中的 `default` 选项设置为 `mailgun`。接下来，确认配置文件包含以下选项：

```php
[
    // ...
    'mailgun' => [
        'transport' => \HyperfExt\Mail\Transport\MailgunTransport::class,
        'options' => [
            'domain' => env('MAIL_MAILGUN_DOMAIN'),
            'key' => env('MAIL_MAILGUN_KEY'),
            // 如果你不使用此「US」区域, 你可以定义自己的区域终端地址：
            // https://documentation.mailgun.com/en/latest/api-intro.html#mailgun-regions
            'endpoint' => env('MAIL_MAILGUN_ENDPOINT', 'api.mailgun.net'),
        ],
    ],
    // ...
];
```

#### Postmark 驱动

要使用 Postmark 驱动， 需要先通过 Composer 安装 Postmark 的 SwiftMailer 函数库：

```shell script
composer require wildbit/swiftmailer-postmark
```

然后，安装 Hyperf Guzzle 并设置 `config/autoload/mail.php` 配置文件中的 `default` 选项。最后, 确认你的配置文件包含以下选项:

```php
[
    // ...
    'postmark' => [
        'transport' => \HyperfExt\Mail\Transport\PostmarkTransport::class,
        'options' => [
            'token' => env('MAIL_POSTMARK_TOKEN'),
        ],
    ],
    // ...
];
```

#### AWS SES 驱动

要使用 AWS SES 驱动，你必须先安装 Amazon AWS SDK。你可以在 `composer.json` 文件的 `require` 段落加入下面这一行并运行 `composer update` 命令：

```
"aws/aws-sdk-php": "~3.0"
```

然后，将 `config/autoload/mail.php` 配置文件的 `default` 选项设置成 `aws_ses` 并确认你的配置文件包含以下选项：

```php
[
    // ...
    'aws_ses' => [
        'transport' => \HyperfExt\Mail\Transport\AwsSesTransport::class,
        'options' => [
            'credentials' => [
                'key' => env('MAIL_AWS_SES_ACCESS_KEY_ID'),
                'secret' => env('MAIL_AWS_SES_SECRET_ACCESS_KEY'),
            ],
            'region' => env('MAIL_AWS_SES_REGION'),
        ],
    ],
    // ...
];
```

如果你在执行 AWS SES `SendRawEmail` 请求的时候需要包含[附加选项](https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-email-2010-12-01.html#sendrawemail )， 你可以在 `aws_ses` 配置中定义一个 `options` 数组：

```php
[
    // ...
    'aws_ses' => [
        'transport' => \HyperfExt\Mail\Transport\AwsSesTransport::class,
        'options' => [
            'credentials' => [
                'key' => env('MAIL_AWS_SES_ACCESS_KEY_ID'),
                'secret' => env('MAIL_AWS_SES_SECRET_ACCESS_KEY'),
            ],
            'region' => env('MAIL_AWS_SES_REGION'),
            // 附加选项
            'options' => [
                'ConfigurationSetName' => 'MyConfigurationSet',
                'Tags' => [
                    [
                        'Name' => 'foo',
                        'Value' => 'bar',
                    ],
                ],
            ],
        ],
    ],
    // ...
];
```

#### 阿里云 DM 驱动

要使用阿里云 DM 驱动，你必须先安装 `alibabacloud/dm`。你可以在 `composer.json` 文件的 `require` 段落加入下面这一行并运行 `composer update` 命令：

```
"alibabacloud/dm": "^1.8"
```

然后，将 `config/autoload/mail.php` 配置文件的 `default` 选项设置成 `aliyun_dm` 并确认你的配置文件包含以下选项：

```php
[
    // ...
    'aliyun_dm' => [
        'transport' => \HyperfExt\Mail\Transport\AliyunDmTransport::class,
        'options' => [
            'access_key_id' => env('MAIL_ALIYUN_DM_ACCESS_KEY_ID'),
            'access_secret' => env('MAIL_ALIYUN_DM_ACCESS_SECRET'),
            'region_id' => env('MAIL_ALIYUN_DM_REGION_ID'),
            'click_trace' => env('MAIL_ALIYUN_DM_CLICK_TRACE', '0'),
        ],
    ],
    // ...
];
```

> 注意，阿里云 DM 驱动仅支持事务类邮件，不支持批量邮件。

<a name="generating-mailables"></a>
## 生成 Mailable 可邮寄类

应用发送的每种邮件都被表示为 `Mailable` 类。这些类存储于 `app/Mail` 目录中。如果您的应用中没有该目录，别慌，当您使用 `gen:mail` 命令生成您的首个 `Mailable` 类时，应用将会自动创建它：

```shell script
php bin/hyperf.php gen:mail OrderShipped
```

<a name="writing-mailables"></a>
## 编写 Mailable 可邮寄类

所有的 `Mailable` 类的配置都在 `build` 方法中完成。您可以通过调用诸如 `from`、`subject`、`view` 和 `attach` 这样的各种各样的方法来配置邮件的内容及其发送。

<a name="configuring-the-sender"></a>
### 配置发件人

#### 使用 `from` 方法

首先，让我们浏览一下邮件的发件人的配置。或者，换句话说，邮件来自谁。有两种方法配置发件人。第一种，您可以在您的 `Mailable` 类的 `build` 方法中使用 `from` 方法：

```php
/**
 * 编译消息。
 */
public function build(): void
{
    $this->from('example@example.com');
}
```
#### 使用全局的 `from` 地址

当然，如果您的应用在任何邮件中使用的「发件人」地址都一致的话，在您生成的每一个 `Mailable` 类中调用 `from` 方法可能会很麻烦。因此，您可以在您的 `config/autoload/mail.php` 文件中指定一个全局的「发件人」地址。当某个 `Mailable` 类没有指定「发件人」时，它将使用该全局「发件人」：

```
'from' => ['address' => 'example@example.com', 'name' => 'App Name'],
```

此外，您亦可在您的 `config/autoload/mail.php` 配置文件中定义一个全局的「回复」地址：

```
'reply_to' => ['address' => 'example@example.com', 'name' => 'App Name'],
```

<a name="configuring-the-body"></a>
### 配置内容

#### 使用视图

得益于 [`hyperf/view`](https://hyperf.wiki/2.0/#/zh-cn/view) 组件的灵活性，您可以在多个受支持的模板引擎中选择适合您的引擎，在您构建您的邮件内容时即可使用模板引擎提供的所有功能及享受其带来的便利性。本组件已经自动依赖了 `hyperf/view` 组件，您需要根据 `hyperf/view` [文档](https://hyperf.wiki/2.0/#/zh-cn/view )的指引安装所需引擎、发布配置文件并完成设置。

> 本文档以 Blade 模板引擎为例。

您可以在 `Mailable` 类的 `build` 方法中使用 `htmlView` 和 `textView` 方法来指定在渲染邮件内容时要使用的模板。

```php
/**
 * 构建邮件消息。
 *
 * @return $this
 */
public function build()
{
    return $this
        // HTML 模板
        ->htmlView('emails.orders.shipped')
        // 纯文本模板
        ->textView('emails.orders.shipped_plain');
}
```

> 您既可定义 HTML 消息也可定义纯文本消息，或者两者同时定义。

#### 视图数据

##### 通过 Public 属性

通常情况下，您可能想要在渲染邮件的内容时传递一些数据到视图中。有两种方法传递数据到时视图中。第一种，您在 `Mailable` 类中定义的所有 `public` 的属性都将自动传递到视图中。因此，举个例子，您可以将数据传递到您的 `Mailable` 类的构造函数中，并将其设置为类的 `public` 属性：
```php
<?php

namespace App\Mail;

use App\Models\Order;
use HyperfExt\Mail\Mailable;

class OrderShipped extends Mailable
{
    /**
     * 订单实例。
     *
     * @var Order
     */
    public $order;

    /**
     * 创建一个消息实例。
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * 构造消息。
     *
     * @return $this
     */
    public function build()
    {
        return $this->htmlView('emails.orders.shipped');
    }
}
```

当数据被设置成为 `public` 属性之后，它将被自动传递到您的视图中，因此您可以像您在 Blade 模板中那样访问它们：

```blade
<div>
    Price: {{ $order->price }}
</div>
```

##### 通过 `with` 方法

如果您想要在邮件数据发送到模板前自定义它们的格式，您可以使用 `with` 方法来手动传递数据到视图中。一般情况下，您还是需要通过 `Mailable` 类的构造函数来传递数据；不过，您应该将它们定义为 `protected` 或 `private` 以防止它们被自动传递到视图中。然后，在您调用 `with` 方法的时候，您可以以数组的形式传递您想要传递给模板的数据：

```php
<?php

namespace App\Mail;

use App\Model\Order;
use HyperfExt\Mail\Mailable;

class OrderShipped extends Mailable
{
    /**
     * 订单实例。
     *
     * @var \App\Models\Order
     */
    protected $order;

    /**
     * 创建一个消息实例。
     *
     * @param  \App\Models\Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * 构造消息。
     *
     * @return $this
     */
    public function build()
    {
        return $this->htmlView('emails.orders.shipped')
                    ->with([
                        'orderName' => $this->order->name,
                        'orderPrice' => $this->order->price,
                    ]);
    }
}
```

当数据使用 `with` 方法传递后，您便可在您的视图中使用它们，此时，您便可以像在 Blade 模板中那样来访问它们：

```blade
<div>
    Price: {{ $orderPrice }}
</div>
```

<a name="attachments"></a>
### 附件

要在邮件中加入附件，在 `build` 方法中使用 `attach` 方法。`attach` 方法接受文件的绝对路径作为它的第一个参数：

```php
/**
 * 构建消息
 *
 * @return $this
 */
public function build()
{
    return $this->htmlView('emails.orders.shipped')
                ->attach('/path/to/file');
}
```

附加文件到消息时，你也可以传递 `数组` 给 `attach` 方法作为第二个参数，以指定显示名称和/或是 MIME 类型：

```php
/**
 * 构建消息
 *
 * @return $this
 */
public function build()
{
    return $this->htmlView('emails.orders.shipped')
                ->attach('/path/to/file', [
                    'as' => 'name.pdf',
                    'mime' => 'application/pdf',
                ]);
}
```

#### 从文件系统中添加附件

该功能依赖 [`hyperf/filesystem`](https://hyperf.wiki/2.0/#/zh-cn/filesystem) 组件，本组件已自动依赖，您需要发布其配置并完成设置。

如果您已在[文件系统](https://hyperf.wiki/2.0/#/zh-cn/filesystem )上存储了一个文件，则可以使用 `attachFromDefaultStorage` 或 `attachFromStorage` 方法将其附加到电子邮件中：

```php
/**
 * 构建消息
 *
 * @return $this
 */
public function build()
{
   return $this->htmlView('emails.orders.shipped')
               // 使用默认存储
               ->attachFromDefaultStorage('/path/to/file')
               // 使用指定存储
               ->attachFromStorage('s3', '/path/to/another_file');
}
```

如有必要，您可以指定文件的附件名称和其他选项：

```php
/**
 * 构建消息
 *
 * @return $this
 */
public function build()
{
   return $this->htmlView('emails.orders.shipped')
               ->attachFromDefaultStorage('/path/to/file', 'name.pdf', [
                   'mime' => 'application/pdf'
               ])
               ->attachFromStorage('s3', '/path/to/another_file', 'name.zip', [
                   'mime' => 'application/zip'
               ]);
}
```

#### 原始数据附件

`attachData` 可以使用字节数据作为附件。例如，你可以使用这个方法将内存中生成而没有保存到磁盘中的 PDF 附加到邮件中。`attachData` 方法第一个参数接收原始字节数据，第二个参数为文件名，第三个参数接受一个数组以指定其他参数：

```php
/**
 * 构建消息
 *
 * @return $this
 */
public function build()
{
    return $this->htmlView('emails.orders.shipped')
                ->attachData($this->pdf, 'name.pdf', [
                    'mime' => 'application/pdf',
                ]);
}
```

### 内联附件

在邮件中嵌入内联图片通常都很麻烦；不过，组件提供了向邮件中附加图片并获取适当的 `CID` 的简便方法。可以使用邮件模板中 `$message` 变量的 `embed` 方法来嵌入内联图片。组件自动使 `$message` 变量在邮件模板中可用，不需要担心如何手动传递它：

```blade
<body>
    Here is an image:

    <img src="{{ $message->embed($pathToImage) }}">
</body>
```

> 注意，请不要在文本消息中使用 `$message`，因为文本消息不能使用内联附件。

#### 嵌入原始数据附件

如果已经有了希望嵌入邮件模板的原始数据串，可以使用 `$message` 变量的 `embedData` 方法：

```blade
<body>
    Here is an image from raw data:

    <img src="{{ $message->embedData($data, $name) }}">
</body>
```

### 自定义 SwiftMailer 消息

`Mailable` 基类的 `withSwiftMessage` 方法允许你注册一个回调，它将在发送消息之前被调用，原始的 SwiftMailer 消息将作为该回调的参数。借此机会，你可以在发消息前对其进行定制。

```php
/**
 * 构建消息
 *
 * @return $this
 */
public function build()
{
    $this->htmlView('emails.orders.shipped');

    $this->withSwiftMessage(function ($message) {
        $message->getHeaders()
                ->addTextHeader('Custom-Header', 'HeaderValue');
    });
}
```

## 发送邮件

若要发送邮件，使用 `Mail` 辅助类的 `to` 方法。`to` 方法接受邮件地址、邮件地址数组以及实现 `HyperfExt/Contract/HasMailAddress` 接口的实例或实例集合。如果传递一个邮件地址数组，那么它必须是包含字符串地址的一位数组或是包含具有 `email` 和 `name` 键的数组的二维数组，mailer 在设置收件人时会自动设置。一旦指定了收件人，就可以将 `Mailable` 类实例传递给 `send` 方法：

```php
<?php

namespace App\Controller;

use App\Mail\OrderShipped;
use App\Model\Order;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use HyperfExt\Mail\Mail;

class OrderController
{
    /**
     * 发送给定的订单。
     */
    public function ship(RequestInterface $request, int $orderId): ResponseInterface
    {
        $order = Order::findOrFail($orderId);

        // 发送订单...

        Mail::to($request->user())->send(new OrderShipped($order));
    }
}
```

在发送消息时不止可以指定收件人。还可以通过链式调用「to」、「cc」、「bcc」一次性指定抄送和密送收件人：

```php
use HyperfExt\Mail\Mail;

Mail::to($request->user())
    ->cc($moreUsers)
    ->bcc($evenMoreUsers)
    ->send(new OrderShipped($order));
```

#### 遍历收件人列表

有时，你需要通过遍历一个收件人/邮件地址数组的方式，给一系列收件人发送邮件。因为 `to` 方法会给 mailable 列表中的收件人追加邮件地址，你应该为每个收件人重建 mailable 实例。

```php
foreach (['taylor@example.com', 'dries@example.com'] as $recipient) {
    Mail::to($recipient)->send(new OrderShipped($order));
}
```

#### 通过特定的 Mailer 发送邮件

默认情况下，组件将使用你的 `mail` 配置文件中配置为 `default` 邮件程序。 但是，你可以使用 `mailer` 方法通过特定的邮件程序配置发送：

```php
Mail::mailer('postmark')
    ->to($request->user())
    ->send(new OrderShipped($order));
```

<a name="queueing-mail"></a>
### 邮件队列

#### 将邮件消息加入队列

由于发送邮件消息可能大幅度增加应用的响应时间，许多开发者选择将邮件消息加入队列放在后台发送。组件使用 [`hyperf/async-queue`](https://hyperf.wiki/2.0/#/zh-cn/async-queue) 简化了这一工作，安装时已经自动依赖，请根据[文档](https://hyperf.wiki/2.0/#/zh-cn/async-queue )进行配置。

若要将邮件消息加入队列，可以在指定消息的接收者后，使用 `Mail` 辅助类的 `queue` 方法：

```php
Mail::to($request->user())
    ->cc($moreUsers)
    ->bcc($evenMoreUsers)
    ->queue(new OrderShipped($order));
```

此方法自动将作业推送到队列中以便消息在后台发送。使用此特性之前，需要[配置队列](https://hyperf.wiki/2.0/#/zh-cn/async-queue )。

> 如果要讲邮件推送到指定队列，可以通过设置 `queue` 方法的第二个参数实现。

#### 延迟消息队列

想要延迟发送队列化的邮件消息，可以使用 `later` 方法。`later` 方法的第二个参数是标示消息延后多少秒后发送：

```php
Mail::to($request->user())
    ->cc($moreUsers)
    ->bcc($evenMoreUsers)
    ->later(new OrderShipped($order), 300); //延后  5 分钟发送
```

> 如果要讲邮件推送到指定队列，可以通过设置 `queue` 方法的第三个参数实现。

#### 默认使用队列

如果你希望你的邮件类始终使用队列，您可以给邮件类 `HyperfExt\Contract\ShouldQueue` 接口，现在即使你调用了 `send` 方法，邮件依旧使用队列的方式发送。另外，如果需要将邮件推送到指定队列，可以设置在邮件类中设置 `queue` 属性。

```php
use HyperfExt\Contract\ShouldQueue;
use HyperfExt\Mail\Mailable;

class OrderShipped extends Mailable implements ShouldQueue
{
    /**
     * 列队名称。
     *
     * @var string
     */
    public $queue = 'default';
}
```

<a name="rendering-mailables"></a>
## 获取邮件内容

有时您可能希望捕获邮件的HTML内容而不发送它。为此，可以调用邮件类的 `render` 方法。此方法将以字符串形式返回邮件类的渲染内容:

```php
$invoice = App\Models\Invoice::find(1);

return Mail::render(new App\Mail\InvoicePaid($invoice));
```

<a name="previewing-mailables-in-the-browser"></a>
### 在浏览器中预览邮件

设计邮件模板时，可以方便地在浏览器中预览邮件，就像典型的 `Blade` 模板一样。因此，组件允许您直接从路由闭包或控制器返回任何邮件类。当邮件返回时，它将渲染并显示在浏览器中，允许您快速预览其设计，而无需将其发送到实际的电子邮件地址

```php
Route::get('mailable', function () {
    $invoice = App\Models\Invoice::find(1);

    return new App\Mail\InvoicePaid($invoice);
});
```

<a name="localizing-mailables"></a>
## 邮件类的本地化

组件允许您以当前语言以外的语言发送邮件，如果是队列邮件，甚至会记住这个区域设置。

为了实现这一点，`Mail` 辅助类提供了一个 `locale` 方法来设置所需的语言。在格式化邮件时，应用程序将更改为该区域设置，然后在格式化完成后恢复到以前的区域设置

> 该特性依赖 [`hyperf/translation`](https://hyperf.wiki/2.0/#/zh-cn/translation )，组件已经自动依赖，请根据[文档](https://hyperf.wiki/2.0/#/zh-cn/translation )指引进行配置。

```php
Mail::to($request->user())->locale('es')->send(
    new OrderShipped($order)
);
```

### 用户的个性化翻译

有时，应用程序会为每个用户存储不同的区域设置。通过在一个或多个模型上实现 `HyperfExt/Contract/HasLocalePreference` 接口，可以指示组件在发送邮件时使用此存储的区域设置:

```php
class User extends Model
{
    /**
     * 返回用户的特定区域信息
     *
     * @return string
     */
    public function getPreferredLocale(): string
    {
        return $this->locale;
    }
}
```

一旦实现了此方法，组件将在向模型发送邮件和通知时自动使用该语言环境。因此，使用此接口时不需要调用 `locale` 方法:

```php
Mail::to($request->user())->send(new OrderShipped($order));
```

<a name="mail-and-local-development"></a>
## 本地开发

当你正在开发一个邮件的应用程序时，您可能不想实际地向真实邮件地址发送邮件。组件提供了几种在本地开发过程中「禁用」实际发送电子邮件的方法

#### 日志驱动

`log` 邮件驱动程序不发送邮件，而是将所有邮件消息写入日志文件用来校验。有关为每个环境配置应用程序的更多信息，请参阅[配置文档](https://hyperf.wiki/2.0/#/zh-cn/logger )。

#### 统一收件人

组件提供的另一个解决方案是为框架发送的所有邮件设置一个统一的收件人。这样应用程序生成的所有电子邮件都将发送到特定的地址，而不是发送消息时指定的地址。你可以在  `config/mail.php` 配置文件中的 `to` 选项来启用：

```
'to' => [
    'address' => 'example@example.com',
    'name' => 'Example'
],
```

#### Mailtrap（虚拟的 smtp 测试服务）

最后，您可以使用像 [Mailtrap](https://mailtrap.io) 这样的服务和 `smtp` 驱动发送邮件消息到「dummy」邮箱中，这样做，您便可以在真实的邮箱客户端中查看您的邮件。此举的好处是允许您在 Mailtrap 的消息查看器中实际查看最终的邮件。

<a name="events"></a>
## 事件

在发送邮件消息的时候，组件会触发两个事件。`MailMessageSending` 事件在发送消息前触发，`MailMessageSent` 事件在消息发送完成后触发。记住，这些事件都是在邮件被**发送**时触发，而不是在队列化的时候。
