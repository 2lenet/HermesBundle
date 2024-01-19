# Common errors

## Why my mail is not sent ?

1. Did you create the mail with the `sending` status (default parameter of create() method) ?

```php
$this->mailer->create($mail);
```

See : [Usage](usage.md "Send mail").

2. Did you run the command to send the mails in queue ?

```bash
bin/console lle:hermes:send
```

See : [Commands](commands.md "Send mails in queue").

2. Do you have a lot a mails in queue ?

If you have a cron, you can juste wait and your mail will bu sent, or you can increase the number of mails by batch :
```bash
bin/console lle:hermes:send --nb=1000
```

See : [Commands](commands.md "Send mails in queue").
