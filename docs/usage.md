# Create and send mails

The following is showing an example how you can use HermesBundle to create and send mails.

## Create mail

First of all you need to instantiate a new MailDto object:

```php
$mail = new MailDto();
$mail->setTemplate('TEMPLATE_CODE');
```

To add recipients to the mail, you need to instantiate new ContactDto objects:

```php
$mail->addTo(new ContactDto($user->getFullName(), $user->getEmail()));
```

To link the mail to an entity:

```php
$mail
    ->setEntityClass('App\Entity\User')
    ->setEntityId(99);
```

## Add data to the mail

As your template can contain variables, you can pass data to your mail or your recipient.
Data from both can be used by the templates. If your template defines three variables : `user.firstname`, `user.lastname` and `url`, you must pass those to your DTO's data.

```php
$mail = new MailDto();
$mail
    ->setTemplate('TEMPLATE_CODE')
    ->setData(['url' => 'https://my.custom.url']);
    
foreach ($users as $user) {
    $contact = new ContactDto($user->getFullName(), $user->getEmail());
    $contact->setData(['user' => $user]);
    $mail->addTo($contact);
}
```

## Send mail

Once the mail has been created, you have to send it with the `create()` method.
The following code will save mail in database and add it to the queue to send :

```php
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Service\Mailer;

public function __construct(
    private readonly Mailer $mailer,
) {
}

public function sendUserMail(): void
{
    $mail = new MailDto();
    // ...
    
    $this->mailer->create($mail);
}
```

## Save mail as draft

If you want to save the mail in database but not want to send it, you can do this with the `create()` method too:

```php
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Service\Mailer;

public function __construct(
    private readonly Mailer $mailer,
) {
}

public function sendUserMail(): void
{
    $mail = new MailDto();
    // ...
    
    $this->mailer->create($mail, MailDto::DRAFT);
}
```

## Send mail immediately

If you want to send a mail without waiting for the cron command (e.g. an user did an action and is waiting for the mail) use the `send()` method:

```php
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Service\Mailer;

public function __construct(
    private readonly Mailer $mailer,
) {
}

public function sendUserMail(): void
{
    $mail = new MailDto();
    // ...

    $this->mailer->send($mail);
}
```

## Add attachment

See [attachments](attachments.md).
